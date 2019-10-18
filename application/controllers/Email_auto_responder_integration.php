<?php
require_once('application/controllers/Home.php');

class Email_auto_responder_integration extends Home
{
	/**
	 * Holds mailchimp list IDs
	 */
	protected $list_ids = [];

	/**
	 * Constructor
	 */
	public function __construct() 
	{
		parent::__construct();

		if ($this->session->userdata('logged_in') != 1)
        redirect('home/login_page', 'location');
        if($this->session->userdata('user_type') != 'Admin' && !in_array(265,$this->module_access))
        redirect('home/login_page', 'location');

		$list_ids = $this->get_mailchimp_list_ids($this->user_id);
		$this->list_ids = $list_ids;
	}

	/**
	 * Displays mailchimp lists imported
	 */
	public function mailchimp_list() 
	{
        $data['body'] = "mail_services/mailchimp/mailchimp";
        $data['page_title'] = $this->lang->line("Mailchimp Integration");
        $this->_viewcontroller($data);   
	}

	/**
	 * Helper method for grid display
	 */
    public function mailchimp_grid_data()
    {
		if (! $this->input->is_ajax_request()) {
			$message = $this->lang->line('Bad Request');
			return $this->customJsonResponse($message);
		}

        $search_value = isset($_POST['search']) ? $_POST['search']['value'] : null;
        $display_columns = ['id', 'tracking_name', 'api_key', 'inserted_at'];
        $search_columns = ['tracking_name'];

        $page = isset($_POST['page']) ? intval($_POST['page']) : 1;
        $start = isset($_POST['start']) ? intval($_POST['start']) : 0;
        $limit = isset($_POST['length']) ? intval($_POST['length']) : 10;

        $sort_index = isset($_POST['order'][0]['column']) ? strval($_POST['order'][0]['column']) : 0;
        $sort = isset($display_columns[$sort_index]) ? $display_columns[$sort_index] : 'mailchimp_config.id';
        $order = isset($_POST['order'][0]['dir']) ? strval($_POST['order'][0]['dir']) : 'ASC';
        $order_by = $sort . " " . $order;

        $where = [
        	'where' => [
        		'mailchimp_config.user_id' => $this->user_id,
        	],
        ];
        if ('' != $search_value) {
            $or_where = [];
            foreach ($search_columns as $key => $value) {
            	$or_where[$value . ' LIKE '] = "%$search_value%";
            }
            $where['or_where'] = $or_where;
        }
            
        $table = 'mailchimp_config';

        $select = [
        	'mailchimp_config.*',
        	'mailchimp_list.list_name',
        	'mailchimp_list.list_id',
        	'users.name'
        ];

        $where = [
        	'where' => [
        		'users.deleted' => '0',
        		'users.id' => $this->user_id,
        	],
        ];

        $join = [
        	'mailchimp_list' => 'mailchimp_config.id=mailchimp_list.mailchimp_config_id,left',
        	'users' => 'mailchimp_config.user_id=users.id,left'
        ];

        $group_by = 'mailchimp_config.id';

        $info = $this->basic->get_data($table, $where, $select, $join, $limit, $start, $order_by, $group_by);

        $total_rows_array = $this->basic->count_row($table, $where, $count = $table . '.id', $join);
        $total_result = $total_rows_array[0]['total_rows'];


        for ($i = 0; $i < sizeof($info); $i++) {
  
        	if ($info[$i]['tracking_name']) {
        		$info[$i]['tracking_name'] = $this->truncate_str($info[$i]['tracking_name']);
        	}

            if ($info[$i]['inserted_at']) {
                $info[$i]['inserted_at'] = date('jS M Y, H:i', strtotime($info[$i]['inserted_at']));
            }

            if (!isset($info[$i]['actions'])) {
                $actions = '';
                $actions .= '<div class="webview-builder-action-buttons">';
                $actions .= '<a data-toggle="tooltip" title="' . $this->lang->line('List') . '" href="#" class="btn btn-circle btn-outline-info" id="mailchimp-details-button" data-tracking-id="' . $info[$i]['id'] . '" target="_blank"><i class="fab fa-wpforms"></i></a>';
                $actions .= '&nbsp;&nbsp;<a data-toggle="tooltip" title="' . $this->lang->line('Refresh') . '" href="#" class="btn btn-circle btn-outline-primary" id="mailchimp-refresh-button" data-tracking-id="' . $info[$i]['id'] . '" data-user-id="' . md5($this->user_id) .'"><i class="fas fa-sync"></i></a>';
                $actions .= '&nbsp;&nbsp;<a data-toggle="tooltip" title="' . $this->lang->line('Delete') . '" href="" class="btn btn-circle btn-outline-danger" id="mailchimp-delete-button" data-tracking-id="'. $info[$i]['id'] . '"><i class="fas fa-trash-alt"></i></a>';
                $actions .= '</div><script>$(\'[data-toggle="tooltip"]\').tooltip();</script>';

                $info[$i]['actions'] = $actions;
            }
        }      

        $data['draw'] = isset($_POST['draw']) ? (int) $_POST['draw'] + 1 : 0;
        $data['recordsTotal'] = $total_result;
        $data['recordsFiltered'] = $total_result;
        $data['data'] = $info;

        echo json_encode($data);
    }

    /**
     * Pulls in mailchimp lists using mailchimp API and then 
     * saves into database
     *
     * @throws \Exception 
     *
     * @return string
     */
	public function mailchimp_add() 
	{	
		if (! $this->input->is_ajax_request()) {
			$message = $this->lang->line('Bad Request');
			return $this->customJsonResponse($message);
		}

		$this->form_validation->set_rules('tracking_name', $this->lang->line('Tracking name'), 'trim|required|min_length[3]|max_length[200]');
		$this->form_validation->set_rules('api_key', $this->lang->line('API Key'), 'trim|required');

		if (false === $this->form_validation->run()) {
			$message = '';
			if ($this->form_validation->error('tracking_name')) {
				$message = $this->form_validation->error('tracking_name');
			} elseif ($this->form_validation->error('api_key')) {
				$message = $this->form_validation->error('api_key');
			}

			$message = strip_tags($message);
			return $this->customJsonResponse($message);
		}

		$api_key = strip_tags($this->input->post('api_key'));
		$tracking_name = strip_tags($this->input->post('tracking_name'));

		$this->load->library('mailchimp_api');
		$mailchimp = $this->mailchimp_api;

		// Pull in group lists from mailchimp
		$values = [];
		$lists = $mailchimp->get_all_list($api_key);
		$lists = json_decode($lists, true);

		if (isset($lists['error']) && true === $lists['error']) {
			$message = $this->lang->line('The API key provided is invalid.');
			return $this->customJsonResponse($message);
		}

		if (null === $lists 
			|| ! is_array($lists) 
			|| ! array_key_exists('lists', $lists)
			|| empty($lists)
		) {
			$message = $this->lang->line('Unable to pull in data from your mailchimp account.');
			return $this->customJsonResponse($message);
		}

		$first_list_id = isset($lists['lists'][0]['id']) ? $lists['lists'][0]['id'] : null;
		if (in_array($first_list_id, $this->list_ids)) {
			$message = $this->lang->line('You have already imported this account.');
			return $this->customJsonResponse($message);
		}

		// Tries to save mailchimp data into database
		try {
			// Begins db transaction
			$this->db->trans_begin();

			$mailchimp_config = $this->basic->insert_data('mailchimp_config', [
				'user_id' => $this->user_id,
				'tracking_name' => $tracking_name,
				'api_key' => $api_key,
				'inserted_at' => date("Y-m-d H:i:s")
			]);

			// Gets last insert ID
			$mailchimp_config_id = $this->db->insert_id(); 

			// Inserts data into database
			$now = date("Y-m-d H:i:s");
			foreach ($lists['lists'] as $key => $list) {
				$data = [
					'mailchimp_config_id' => $mailchimp_config_id,
					'list_name' => $list['name'],
					'list_id' => $list['id'],
					'inserted_at' => $now,
				];

				$this->basic->insert_data('mailchimp_list', $data);
			}

			// Perform commit or rollback on transaction's status
			if (false === $this->db->trans_status()) {
				$this->db->trans_rollback();
			} else {
				$this->db->trans_commit();
			}
		} catch (\Exception $e) {
			log_message('error', 'Unable to operate mailchimp data.');
			return $this->customJsonResponse($e->getMessage());
		}

		$this->_insert_usage_log(265,1);
		$message = $this->lang->line('You mailchimp account has been added successfully.');
		return $this->customJsonResponse($message, true);
	}

	/**
	 * Displays mailchimp lists
	 *
	 * @return string
	 */
	public function mailchimp_details() 
	{
		if (! $this->input->is_ajax_request()) {
			$message = $this->lang->line('Bad Request');
			return $this->customJsonResponse($message);
		}

		$this->form_validation->set_rules('tracking_id', 'Tracking ID', 'trim|required|numeric');

		if (false === $this->form_validation->run()) {
			$message = $this->form_validation->error('tracking_id');
			$message = strip_tags($message);

			return $this->customJsonResponse($message);
		}

		$tracking_id = (int) $this->input->post('tracking_id');

		$select = [
			'mailchimp_list.list_name',
			'mailchimp_list.list_id',
			"DATE_FORMAT(mailchimp_list.inserted_at, '%D %b, %Y') as inserted_at",
			'mailchimp_config.tracking_name',
			'mailchimp_config.user_id',
		];

		$where = [
			'where' => [
				'mailchimp_config.user_id' => $this->user_id,
				'mailchimp_list.mailchimp_config_id' => $tracking_id,
			],
		];

		$join = [
			'mailchimp_config' => 'mailchimp_list.mailchimp_config_id=mailchimp_config.id,left',
		];

		$results = $this->basic->get_data('mailchimp_list', $where, $select, $join);

		echo json_encode($results);
	}

	/**
	 * Refreshes mailchimp account data
	 *
	 * @return string
	 */
	public function mailchimp_refresh() 
	{
		if (! $this->input->is_ajax_request()) {
			$message = $this->lang->line('Bad Request');
			return $this->customJsonResponse($message);
		}

		$this->form_validation->set_rules('user_id', 'User ID', 'trim|required');
		$this->form_validation->set_rules('tracking_id', 'Tracking ID', 'trim|required|numeric');

		if (false === $this->form_validation->run()) {
			if ($this->form_validation->error('user_id')) {
				$message = $this->form_validation->error('user_id');
			} elseif ($this->form_validation->error('tracking_id')) {
				$message = $this->form_validation->error('tracking_id');
			}

			$message = strip_tags($message);

			return $this->customJsonResponse($message);
		}

		$user_id = (string) $this->input->post('user_id');
		$tracking_id = (int) $this->input->post('tracking_id');

		if ($user_id != md5($this->user_id)) {
			$message = $this->lang->line('Bad Request');
			return $this->customJsonResponse($message);
		}

		$select = [
			'api_key'
		];

		$where = [
			'where' => [
				'id' => $tracking_id,
				'user_id' => $this->user_id,
			],
		];

		$results = $this->basic->get_data('mailchimp_config', $where, $select, [], 1);

		if (1 != count($results)) {
			$message = $this->lang->line('Bad Request');
			return $this->customJsonResponse($message);
		}

		$this->load->library('mailchimp_api');
		$mailchimp = $this->mailchimp_api;

		// Pull in group lists from mailchimp
		$values = [];
		$api_key = isset($results[0]['api_key']) ? $results[0]['api_key'] : null;

		$lists = $mailchimp->get_all_list($api_key);

		$lists = json_decode($lists, true);

		if (null === $lists 
			|| ! is_array($lists) 
			|| ! array_key_exists('lists', $lists)
			|| empty($lists)
		) {
			$message = $this->lang->line('Unable to pull in data from your mailchimp account.');
			return $this->customJsonResponse($message);
		}

		// Tries to save mailchimp data into database
		try {
			// Begins db transaction
			$this->db->trans_begin();

			// Prepare values columns
			$now = date("Y-m-d H:i:s");
			foreach ($lists['lists'] as $key => $list) {
				$list_name= $this->db->escape($list['name']) ; 
				$value = "('{$tracking_id}', $list_name, '{$list['id']}', '{$now}')";

				array_push($values, $value);
			}			

			$sql = "INSERT INTO `mailchimp_list` (`mailchimp_config_id`, `list_name`, `list_id`, `inserted_at`) VALUES " . join(',', $values) . " ON DUPLICATE KEY UPDATE `mailchimp_config_id`=VALUES(`mailchimp_config_id`), `list_name`=VALUES(`list_name`), `list_id`=VALUES(`list_id`), `inserted_at`=VALUES(`inserted_at`);";

			// Runs the query
			$this->db->query($sql);

			// Perform commit or rollback on transaction's status
			if (false === $this->db->trans_status()) {
				$this->db->trans_rollback();
			} else {
				$this->db->trans_commit();
			}
		} catch (\Exception $e) {
			log_message('error', 'Unable to operate mailchimp data.');
			return $this->customJsonResponse($e->getMessage());
		}

		$message = $this->lang->line('Your mailchimp account has been refreshed successfully.');
		return $this->customJsonResponse($message, true);
	}

	/**
	 * Deletes mailchimp lists
	 *
	 * @return string
	 */
	public function mailchimp_delete() 
	{
		if (! $this->input->is_ajax_request()) {
			$message = $this->lang->line('Bad Request');
			return $this->customJsonResponse($message);
		}

		$this->form_validation->set_rules('tracking_id', 'Tracking ID', 'trim|required|numeric');

		if (false === $this->form_validation->run()) {
			$message = $this->form_validation->error('tracking_id');
			$message = strip_tags($message);

			return $this->customJsonResponse($message);
		}

		$tracking_id = (int) $this->input->post('tracking_id');

		$select = [
			'user_id',
		];

		$where = [
			'where' => [
				'id' => $tracking_id,
				'user_id' => $this->user_id,
			],
		];

		$results = $this->basic->get_data('mailchimp_config', $where, $select, [], 1);

		if (count($results) != 1) {
			$message = $this->lang->line('You do not have permission to delete this account.');
			return $this->customJsonResponse($message);
		}

		if ($this->basic->delete_data('mailchimp_config', ['id' => $tracking_id])) {
			$this->_delete_usage_log(265,1);
			$message = $this->lang->line('Your mailchimp account has been deleted successfully.');
			return $this->customJsonResponse($message, true);
		}

		$message = $this->lang->line('Something went wrong! Please try again.');
		return $this->customJsonResponse($message);
	}
	/**
	 * Truncates string 
	 *
	 * @param string $str The string to be truncated
	 * @param string $delimiter The trailing string delimiter
	 * @param string $encoding The encodign type
	 * @return string
	 */
	public function truncate_str ($str, $delimiter = '...', $encoding = 'UTF-8') 
	{
	    $truncated_str = mb_substr($str, 0, 60, $encoding);
	    
	    if (mb_strlen($truncated_str) < 60) {
	    	$delimiter = '';
	    }

		if (" " === mb_substr($truncated_str, -1, null, $encoding)) {
			return mb_substr($str, 0, 59, $encoding) . $delimiter;
		}

	    return $truncated_str . $delimiter;
	}

	/**
	 * Gets an array of mailchimp list IDs
	 *
	 * @param integer $user_id The user ID
	 * @return array
	 */
	protected function get_mailchimp_list_ids($user_id) 
	{
		$select = [
			'mailchimp_list.list_id',
		];

		$where = [
			'where' => [
				'mailchimp_config.user_id' => $user_id,
			],
		];

		$join = [
			'mailchimp_config' => 'mailchimp_list.mailchimp_config_id=mailchimp_config.id,left',
		];

		$results = $this->basic->get_data('mailchimp_list', $where, $select, $join);

		$ids = array_map(function($item) {
			return $item['list_id'];
		}, $results);

		return $ids ? $ids : [];
	}

	/**
	 * Produces custom json response
	 *
	 * @param string $message
	 * @param bool $success
	 * @return void
	 */
	protected function customJsonResponse($message, $success = false) 
	{
		echo json_encode([
			'error' => $success ? false : true,
			'success' => $success,
			'message' => $message
		]);
	}	



		
}