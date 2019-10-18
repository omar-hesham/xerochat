<?php 

require_once("Home.php"); // loading home controller

class Sms_email_manager extends Home
{
    public $user_id;

    public function __construct()
    {

        parent::__construct();
        
        if ($this->session->userdata('logged_in') != 1)
            redirect('home/login_page', 'location');    

        // if($this->session->userdata('user_type') != 'Admin' && !in_array(264,$this->module_access))
        //     redirect('home/login_page', 'location');

        $this->load->library('Sms_manager');

        set_time_limit(0);
        $this->important_feature();
        $this->member_validity();
    }

    public function index()
    {
        if($this->session->userdata('user_type') != 'Admin' && count(array_intersect($this->module_access, array('263','264')))==0)
            redirect('home/login_page', 'location');

        $data['body'] = 'sms_email_manager/section_menu_block';
        $data['page_title'] = $this->lang->line('SMS/Email Manager');
        $this->_viewcontroller($data);
    }

    // SMS API Section Started
    public function sms_api_lists()
    {
        if($this->session->userdata('user_type') != 'Admin' && !in_array(264,$this->module_access))
            redirect('home/login_page', 'location');

        $data['body'] = 'sms_email_manager/sms/sms_api';
        $data['gateway_lists'] = $this->_api_gateways();
        $data['page_title'] = $this->lang->line('SMS API');
        $this->_viewcontroller($data);   
    }

    public function sms_api_list_data()
    {
        if($this->session->userdata('user_type') != 'Admin' && !in_array(264,$this->module_access)) exit;
        
        $this->ajax_check();

        $search_value = $_POST['search']['value'];
        $display_columns = array("#",'id','gateway_name','phone_number','status','actions');
        $search_columns = array('gateway_name');

        $page = isset($_POST['page']) ? intval($_POST['page']) : 1;
        $start = isset($_POST['start']) ? intval($_POST['start']) : 0;
        $limit = isset($_POST['length']) ? intval($_POST['length']) : 10;
        $sort_index = isset($_POST['order'][0]['column']) ? strval($_POST['order'][0]['column']) : 2;
        $sort = isset($display_columns[$sort_index]) ? $display_columns[$sort_index] : 'id';
        $order = isset($_POST['order'][0]['dir']) ? strval($_POST['order'][0]['dir']) : 'desc';
        $order_by=$sort." ".$order;

        $where_simple = array();

        $where_simple['deleted'] = '0';
        $where_simple['user_id'] = $this->user_id;

        if($search_value != "")
        {
        	foreach ($search_columns as $key => $value) 
        		$where_simple[$value.' LIKE '] = "%$search_value%";
        }

        $where  = array('where'=>$where_simple);

        $table = "sms_api_config";
        $info = $this->basic->get_data($table,$where,$select='',$join='',$limit,$start,$order_by,$group_by='');

        $total_rows_array = $this->basic->count_row($table,$where,$count="id",$join="",$group_by='');
        $total_result=$total_rows_array[0]['total_rows'];

        // 'username_auth_id','password_auth_token','api_id','remaining_credetis',

        for($i = 0; $i < count($info); $i++)
        {
            $status = $info[$i]["status"];
            if($status=='1') $info[$i]["status"] = "<i title ='".$this->lang->line('Active')."'class='status-icon fas fa-toggle-on text-primary'></i>";
            else $info[$i]["status"] = "<i title ='".$this->lang->line('Inactive')."'class='status-icon fas fa-toggle-off gray'></i>";

            if(isset($info[$i]['phone_number']) && $info[$i]['phone_number'] !="")
                $info[$i]['phone_number'] = $info[$i]['phone_number'];
            else
                $info[$i]['phone_number'] = "-";

            $info[$i]['actions'] = "<div style='min-width:100px;'><a href='#' title='".$this->lang->line("View Details")."' class='btn btn-circle btn-outline-primary see_api_details' table_id='".$info[$i]['id']."'><i class='fas fa-info-circle'></i></a>&nbsp;&nbsp;";

            $info[$i]['actions'] .= "<a href='#' title='".$this->lang->line("Edit API")."' class='btn btn-circle btn-outline-warning edit_api' table_id='".$info[$i]['id']."'><i class='fa fa-edit'></i></a>&nbsp;&nbsp;";

            $info[$i]['actions'] .= "<a href='#' title='".$this->lang->line("Delete API")."' class='btn btn-circle btn-outline-danger delete_api' table_id='".$info[$i]['id']."'><i class='fa fa-trash-alt'></i></a></div>
            	<script>$('[data-toggle=\"tooltip\"]').tooltip();</script>";
        }

        $data['draw'] = (int)$_POST['draw'] + 1;
        $data['recordsTotal'] = $total_result;
        $data['recordsFiltered'] = $total_result;
        $data['data'] = convertDataTableResult($info, $display_columns ,$start,$primary_key="id");

        echo json_encode($data);
    }

    public function api_infos()
    {
        if($this->session->userdata('user_type') != 'Admin' && !in_array(264,$this->module_access)) exit;

    	$this->ajax_check();

    	$res = array();
    	$table_id = $this->input->post("table_id");

    	$where_simple = array();

    	$where_simple['deleted'] = '0';
    	$where_simple['user_id'] = $this->user_id;
    	$where_simple['id'] = $table_id;

    	$where  = array('where'=>$where_simple);

    	$api_info = $this->basic->get_data("sms_api_config",$where);
    	// 'username_auth_id','password_auth_token','api_id','remaining_credetis',

    	$res['username_auth_id'] = $api_info[0]['username_auth_id'];
    	$res['password_auth_token'] = $api_info[0]['password_auth_token'];
    	$res['api_id'] = $api_info[0]['api_id'];

    	$this->sms_manager->set_credentioal($table_id,$this->user_id);

    	$res['remaining_credetis'] = "-";
    	if($api_info[0]['gateway_name'] == "plivo") 
    		$res['remaining_credetis'] = $this->sms_manager->get_plivo_balance();
    	if($api_info[0]['gateway_name'] == "clickatell") 
    		$res['remaining_credetis'] = $this->sms_manager->get_clickatell_balance();
    	if($api_info[0]['gateway_name'] == "clickatell-platform") 
    		$res['remaining_credetis'] = $this->sms_manager->get_clickatell_platform_balance();
    	if($api_info[0]['gateway_name'] == "nexmo") 
    		$res['remaining_credetis'] = $this->sms_manager->get_nexmo_balance();
    	if($api_info[0]['gateway_name'] == "africastalking.com") 
    		$res['remaining_credetis'] = $this->sms_manager->africastalking_sms_balance();
    	if($api_info[0]['gateway_name'] == "infobip.com") 
    		$res['remaining_credetis'] = $this->sms_manager->infobip_balance_check();
    	if($api_info[0]['gateway_name'] == "Shreeweb") 
    		$res['remaining_credetis'] = $this->sms_manager->get_shreeweb_balance();

    	echo json_encode($res);
    }


    public function ajax_create_sms_api()
    {
        if($this->session->userdata('user_type') != 'Admin' && !in_array(264,$this->module_access)) exit;

    	$this->ajax_check();

    	$return_response = array();

    	if($_POST)
    	{
    		$post = $_POST;
    		foreach ($post as $key => $value) 
    		{
    		    $$key = trim($this->input->post($key,TRUE));
    		}

            $status_checked = $this->input->post("status");
            if($status_checked == "") $status_checked = "0";

    		$inserted_data = array();
    		$inserted_data['user_id'] = $this->user_id;
    		$inserted_data['gateway_name'] = $gateway_name;
    		$inserted_data['username_auth_id'] = $username_auth_id;
    		$inserted_data['password_auth_token'] = $password_auth_token;
    		$inserted_data['api_id'] = $api_id;
    		$inserted_data['phone_number'] = $phone_number;
    		$inserted_data['status'] = $status_checked;

    		if($this->basic->insert_data("sms_api_config",$inserted_data))
    		{
    			$return_response['status'] = "1";
    			$return_response['msg']  = $this->lang->line('New API Information has been added successfully');
    			
    		} else
    		{
    			$return_response['status'] = "0";
    			$return_response['msg']  = $this->lang->line('Something went wrong, please try again.');
    		}

    		echo json_encode($return_response);
    	}

    }

    public function ajax_get_api_info_for_update()
    {
        if($this->session->userdata('user_type') != 'Admin' && !in_array(264,$this->module_access)) exit;

    	$this->ajax_check();

    	$table_id = $this->input->post("table_id",true);
    	$gateway_lists = $this->_api_gateways();

    	$where['where'] = array("id"=>$table_id,'user_id'=>$this->user_id);

    	$get_info = $this->basic->get_data("sms_api_config",$where);
    	$gateway_name = isset($get_info[0]['gateway_name']) ? $get_info[0]['gateway_name']: "";
    	$username_auth_id = isset($get_info[0]['username_auth_id']) ? $get_info[0]['username_auth_id']:"";
    	$password_auth_token = isset($get_info[0]['password_auth_token']) ? $get_info[0]['password_auth_token']: "";
    	$api_id = isset($get_info[0]['api_id']) ? $get_info[0]['api_id']: "";
    	$phone_number = isset($get_info[0]['phone_number']) ? $get_info[0]['phone_number']: "";
    	$status = $get_info[0]['status'];

        if($status == "1") $status_checked = "checked";
        else $status_checked = "";

    	$update_data_form = '<div class="row">
                    <div class="col-12">                    
                        <form action="#" enctype="multipart/form-data" id="update_sms_api_form" method="post">
                        	<input type="hidden" name="table_id" id="table_id" value="'.$get_info[0]['id'].'">
                            <div class="row">
                                <div class="col-12 col-md-6">
                                    <div class="form-group">
                                        <label>'.$this->lang->line('Gateway Name').'</label>'.
                            form_dropdown("gateway_name",$gateway_lists,$gateway_name, "class='form-control select2' id='updated_gateway_name' style='width:100%;'");


        $update_data_form .= '</div>
                                </div>
                                <div class="col-12 col-md-6">
                                    <div class="form-group">
                                        <label>'.$this->lang->line('Auth ID/ Auth Key/ API Key/ MSISDN/ Account SID/ Account ID/ Username/ Admin').'</label>
                                        <input type="text" class="form-control" name="username_auth_id" id="updated_username_auth_id" value="'.$username_auth_id.'">
                                    </div>
                                </div>
                                <div class="col-12 col-md-6">
                                    <div class="form-group">
                                        <label>'.$this->lang->line('Auth Token/ API Secret/ Password').'</label>
                                        <input type="text" class="form-control" name="password_auth_token" id="updated_password_auth_token" value="'.$password_auth_token.'">
                                    </div>
                                </div>
                                <div class="col-12 col-md-6">
                                    <div class="form-group">
                                        <label>'.$this->lang->line('API ID').'</label>
                                        <input type="text" class="form-control" name="api_id" id="updated_api_id" value="'.$api_id.'">
                                    </div>
                                </div>
                                <div class="col-12 col-md-6">
                                    <div class="form-group">
                                        <label>'.$this->lang->line('Sender/ Sender ID/ Mask/ From').'</label>
                                        <input type="text" class="form-control" name="phone_number" id="updated_phone_number" value="'.$phone_number.'">
                                    </div>
                                </div>
                                <div class="col-12 col-md-6">
                                    <div class="form-group">
                                        <label style="margin-bottom:20px;">'.$this->lang->line('Status').'</label><br>
                                        <label class="custom-switch">
                                            <input type="checkbox" name="status" value="1" id="status" class="custom-switch-input" '.$status_checked.'>
                                            <span class="custom-switch-indicator"></span>
                                            <span class="custom-switch-description">'.$this->lang->line('Active').'</span>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
                <script>$("#updated_status,#updated_gateway_name").select2()</script>';

        echo $update_data_form;
    }

    public function ajax_update_sms_api()
    {
        if($this->session->userdata('user_type') != 'Admin' && !in_array(264,$this->module_access)) exit;

        $this->ajax_check();

        $return_response = array();

        $table_id = $this->input->post("table_id",true);

        if($_POST)
        {
            $post = $_POST;
            foreach ($post as $key => $value) 
            {
                $$key = trim($this->input->post($key,TRUE));
            }

            $status_checked = $this->input->post("status");
            if($status_checked == "") $status_checked = "0";

            $updated_data = array();
            $updated_data['user_id'] = $this->user_id;
            $updated_data['gateway_name'] = $gateway_name;
            $updated_data['username_auth_id'] = $username_auth_id;
            $updated_data['password_auth_token'] = $password_auth_token;
            $updated_data['api_id'] = $api_id;
            $updated_data['phone_number'] = $phone_number;
            $updated_data['status'] = $status_checked;

            $where = array("user_id"=>$this->user_id,'id'=>$table_id);

            if($this->basic->update_data("sms_api_config",$where,$updated_data))
            {
                $return_response['status'] = "1";
                $return_response['msg']  = $this->lang->line('API Information has been updated successfully');
                
            } else
            {
                $return_response['status'] = "0";
                $return_response['msg']  = $this->lang->line('Something went wrong, please try again.');
            }

            echo json_encode($return_response);
        }
    }
    

    public function delete_sms_api()
    {
        if($this->session->userdata('user_type') != 'Admin' && !in_array(264,$this->module_access)) exit;

        $this->ajax_check();

        $table_id = $this->input->post("table_id",true);

        if($table_id == "" || $table_id == "0") exit;

        if($this->basic->delete_data("sms_api_config",array("id"=>$table_id)))
        {
            echo "1";
        } else
        {
            echo "0";
        }
    }
    // End of the SMS API section


    // Phonebook section started
    public function contact_group_list()
    {
        if($this->session->userdata('user_type') != 'Admin' && count(array_intersect($this->module_access, array('263','264')))==0)
            redirect('home/login_page', 'location');

        $per_page = 10;
        $search_value = "";

        // set per_page and search_value from user_submission
        if (isset($_POST['rows_number']) || isset($_POST['search_value'])) {

            $per_page = $this->input->post('rows_number', true);
            $search_value = $this->input->post('search_value', true);

            $this->session->set_userdata('sms_email_contact_group_per_page', $per_page);
            $this->session->set_userdata('sms_email_contact_group_search_value', $search_value);
        }


        // set session so that pagination can get proper per_page & search_value
        if ($this->session->userdata('sms_email_contact_group_per_page')) 
            $per_page = $this->session->userdata('sms_email_contact_group_per_page');

        if ($this->session->userdata('sms_email_contact_group_search_value')) 
            $search_value = $this->session->userdata('sms_email_contact_group_search_value');

        $where['where'] = array('user_id' => $this->user_id, 'type LIKE' => '%'.$search_value.'%');


        $total_group = $this->basic->get_data('sms_email_contact_group', $where,'','','','','id DESC');


        if ($per_page == 'all')
            $per_page = count($total_group);

        /* set cinfiguration for pagination */
        $config = array(
            'uri_segment' => 3,
            'base_url' => base_url('sms_email_manager/contact_group_list/'),
            'total_rows' => count($total_group),
            'per_page' => $per_page,

            'full_tag_open' => '<ul class="pagination">',
            'full_tag_close' => '</ul>',

            'first_link' => $this->lang->line('First Page'),
            'first_tag_open' => '<li class="page-item">',
            'first_tag_close' => '</li>',

            'last_link' => $this->lang->line('Last Page'),
            'last_tag_open' => '<li class="page-item">',
            'last_tag_close' => '</li>',

            'next_link' => $this->lang->line('Next'),
            'next_tag_open' => '<li class="page-item">',
            'next_tag_close' => '</li>',

            'prev_link' => $this->lang->line('Previous'),
            'prev_tag_open' => '<li class="page-item">',
            'prev_tag_close' => '</li>',

            'cur_tag_open' => '<li class="page-item active"><a class="page-link">',
            'cur_tag_close' => '</a></li>',

            'num_tag_open' => '<li class="page-item">',
            'num_tag_close' => '</li>',
            'attributes' => array('class' => 'page-link')
        );
        $this->pagination->initialize($config);
        $page_links = $this->pagination->create_links();


        $start = $this->uri->segment(3);
        $limit = $config['per_page'];

        $contact_group = $this->basic->get_data('sms_email_contact_group', array('where' => array('user_id' => $this->user_id, 'type LIKE' => '%'.$search_value.'%')), '', '', $limit, $start, 'id DESC');

        $data['page_title'] = $this->lang->line("Contact Group");
        $data['contactGroups'] = $contact_group;
        $data['page_links'] = $page_links;
        $data['per_page'] = ($per_page == count($total_group)) ? 'all' : $per_page;
        $data['search_value'] = $search_value;
        $data['body'] = "sms_email_manager/contact_book/contact_group";


        $this->_viewcontroller($data);
    }

    public function add_contact_group_action()
    {
        if($this->session->userdata('user_type') != 'Admin' && count(array_intersect($this->module_access, array('263','264')))==0) exit;

        $this->ajax_check();
        $group_name = trim(addslashes(strip_tags($this->input->post("group_name"))));
        $in_data = array(
            'user_id' => $this->user_id,
            'type' => $group_name,
            'created_at' => date("Y-m-d H:i:s")
        );

        if((isset($group_name) && !empty($group_name)) && $this->basic->is_exist("sms_email_contact_group",$where=array("user_id"=>$this->user_id,"type"=>$group_name)))
        {
            echo "2";
            exit;
        }

        if($this->basic->insert_data("sms_email_contact_group", $in_data))
        {
            echo "1";
        } else
        {
            echo "0";
        }
    }

    public function ajax_get_group_info()
    {
        if($this->session->userdata('user_type') != 'Admin' && count(array_intersect($this->module_access, array('263','264')))==0) exit;

        $this->ajax_check();
        $group_id = $this->input->post("group_id");
        
        if($group_id == "0" || $group_id == "") exit;

        $group_info =$this->basic->get_data("sms_email_contact_group", array('where'=>array("id"=>$group_id, 'user_id'=> $this->user_id)));
        $updateForm = '<div class="row">
                    <div class="col-12">
                        <div class="form-group">
                            <label>'.$this->lang->line('Group Name').'</label>
                            <input type="text" class="form-control" name="group_name" id="update_group_name" value="'.$group_info[0]["type"].'">
                            <input type="hidden" class="form-control" name="table_id" id="table_id" value="'.$group_id.'">
                        </div>
                    </div>
                </div>';

        echo $updateForm;
    }

    public function ajax_update_group_info()
    {
        if($this->session->userdata('user_type') != 'Admin' && count(array_intersect($this->module_access, array('263','264')))==0) exit;

        $this->ajax_check();

        $table_id = $this->input->post("table_id");

        if($table_id == "0" || $table_id == "") exit;

        $group_name = trim(addslashes(strip_tags($this->input->post("group_name"))));

        $oldData = $this->basic->get_data("sms_email_contact_group", array("where"=>array("id"=>$table_id,"user_id"=>$this->user_id)),array("type"));

        if($oldData[0]['type'] != $group_name)
        {
            if((isset($group_name) && !empty($group_name)) && $this->basic->is_exist("sms_email_contact_group",$where=array("user_id"=>$this->user_id,"type"=>$group_name)))
            {
                echo "2";
                exit;
            }
        }

        if($this->basic->update_data("sms_email_contact_group",array("id"=>$table_id,'user_id'=>$this->user_id), array("type"=>$group_name)))
        {
            echo "1";
        } else
        {
            echo "0";
        }
    }

    public function delete_contact_group()
    {
        if($this->session->userdata('user_type') != 'Admin' && count(array_intersect($this->module_access, array('263','264')))==0) exit;

        $this->ajax_check();

        $table_id = $this->input->post("table_id");
        if($table_id == "0" || $table_id == "") exit;

        if($this->basic->delete_data("sms_email_contact_group",array("id"=>$table_id)))
        {
            echo "1";
        } else
        {
            echo "0";
        }
    }

    public function contact_list()
    {
        if($this->session->userdata('user_type') != 'Admin' && count(array_intersect($this->module_access, array('263','264')))==0)
            redirect('home/login_page', 'location');

        $data['body'] = 'sms_email_manager/contact_book/contact_lists';

        $table = 'sms_email_contact_group';
        $where['where'] = array('user_id'=>$this->user_id);

        $info = $this->basic->get_data($table,$where);

        foreach ($info as $key => $value) 
        {
            $result = $value['id'];
            $data['contact_group_lists'][$result] = $value['type'];
        }

        $data['page_title'] = $this->lang->line('Contact Book');
        $this->_viewcontroller($data);
    }

    public function contact_lists_data()
    {
        if($this->session->userdata('user_type') != 'Admin' && count(array_intersect($this->module_access, array('263','264')))==0) exit;
        $this->ajax_check();

        $group_id = trim($this->input->post("group_id",true));
        $searching = trim($this->input->post("contact_list_searching",true));
        $display_columns = array("#",'CHECKBOX','id','first_name','last_name','email','phone_number','contact_type_id','actions');
        $search_columns = array('first_name', 'last_name','phone_number','email');

        $page = isset($_POST['page']) ? intval($_POST['page']) : 1;
        $start = isset($_POST['start']) ? intval($_POST['start']) : 0;
        $limit = isset($_POST['length']) ? intval($_POST['length']) : 10;
        $sort_index = isset($_POST['order'][0]['column']) ? strval($_POST['order'][0]['column']) : 2;
        $sort = isset($display_columns[$sort_index]) ? $display_columns[$sort_index] : 'id';
        $order = isset($_POST['order'][0]['dir']) ? strval($_POST['order'][0]['dir']) : 'desc';
        $order_by=$sort." ".$order;

        $where_simple = array();
        $where_simple['user_id'] = $this->user_id;

        if ($group_id) 
        {
            // $where_simple['contact_type_id like ']    = "%".$group_id."%";
            $this->db->where("FIND_IN_SET('$group_id',sms_email_contacts.contact_type_id) !=", 0);
        }

        $sql = '';
        if ($searching != '')
        {
            $sql = "(first_name LIKE  '%".$searching."%' OR last_name LIKE '%".$searching."%' OR phone_number LIKE '%".$searching."%' OR email LIKE '%".$searching."%')";
        }
        if($sql != '') $this->db->where($sql);

        $where = array('where' => $where_simple);

        $table = "sms_email_contacts";
        $info = $this->basic->get_data($table,$where,$select='',$join='',$limit,$start,$order_by,$group_by='');

        $total_rows_array = $this->basic->count_row($table,$where,$count="id",$join="",$group_by='');
        $total_result=$total_rows_array[0]['total_rows'];

        foreach ($info as $key => $value) 
        {
            $info[$key]['email'] = "<div style='min-width:150px'>".$info[$key]['email']."</div>";
            $info[$key]['actions'] = "<div style='min-width:100px'><a href='#' title='".$this->lang->line("Edit Contact")."' class='btn btn-circle btn-outline-warning edit_contact' table_id='".$info[$key]['id']."'><i class='fa fa-edit'></i></a>&nbsp;&nbsp;";

            $info[$key]['actions'] .= "<a href='#' title='".$this->lang->line("Delete Contact")."' class='btn btn-circle btn-outline-danger delete_contact' table_id='".$info[$key]['id']."'><i class='fa fa-trash-alt'></i></a></div>
                <script>$('[data-toggle=\"tooltip\"]').tooltip();</script>";

            $groupids = $info[$key]['contact_type_id'];

            $type_id = explode(",",$groupids);

            $table = 'sms_email_contact_group';
            $select = array('type');

            $where_group['where_in'] = array('id'=>$type_id);
            $where_group['where'] = array('deleted'=>'0');

            $info1 = $this->basic->get_data($table,$where_group,$select);

            $str = '';
            foreach ($info1 as  $value1)
            {
                $str.= $value1['type'].", ";
            }

            $str = trim($str, ", ");

            $info[$key]['contact_type_id'] = $str;
        }

        $data['draw'] = (int)$_POST['draw'] + 1;
        $data['recordsTotal'] = $total_result;
        $data['recordsFiltered'] = $total_result;
        $data['data'] = convertDataTableResult($info, $display_columns ,$start,$primary_key="id");

        echo json_encode($data);
    }

    public function ajax_export_contacts()
    {
        if($this->session->userdata('user_type') != 'Admin' && count(array_intersect($this->module_access, array('263','264')))==0) exit;

        $this->ajax_check();

        $table = 'sms_email_contacts';       
        $selected_contact_data = $this->input->post('info', true);
        $url_names_array = array();

        foreach ($selected_contact_data as  $value) 
        {
            $id_array[] = $value;
        }

        $where['where_in'] = array('id' => $id_array);

        $info = $this->basic->get_data('sms_email_contacts',$where);
        $info_count = count($info);

        for($i=0; $i<$info_count; $i++)
        {
            $group_ids = $info[$i]['contact_type_id'];
            $exploded_group_ids = explode(",",$group_ids);

            $table = 'sms_email_contact_group';
            $select = array('type');

            $where_group['where_in'] = array('id'=>$exploded_group_ids);
            $where_group['where'] = array('deleted'=>'0');

            $info1 = $this->basic->get_data($table,$where_group,$select);

            $str = '';
            foreach ($info1 as  $value1)
            {
                $str .= $value1['type'].","; 
            }

            $str = trim($str, ",");

            $info[$i]['contact_type_id'] = $str;
        }

        $dir_name = FCPATH."download/contact_export/";

        if(!file_exists($dir_name))
        {
            mkdir($dir_name,0777);
        }

        $file_name = "download/contact_export/exported_contact_list_".time()."_".$this->user_id.".csv";
        $fp = fopen($file_name, "w");
        $head = array("first_name","last_name","phone_number","email");
        fputcsv($fp, $head);
        $write_info = array();

        foreach ($info as  $value) 
        {
            $write_info = array();            
            $write_info[] = $value['first_name'];
            $write_info[] = $value['last_name'];
            $write_info[] = $value['phone_number'];
            $write_info[] = $value['email'];   
            fputcsv($fp, $write_info);  
        }

        fclose($fp);  
        echo $file_name;
    }

    public function ajax_import_csv_files()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'GET') exit();

        if($this->session->userdata('user_type') != 'Admin' && count(array_intersect($this->module_access, array('263','264')))==0) exit;

        $ret = array();
        $output_dir = FCPATH."upload/csv";

        if(!file_exists($output_dir))
        {
            mkdir($output_dir,0777);
        }

        if (isset($_FILES["file"])) {

            $error = $_FILES["file"]["error"];

            $post_fileName = $_FILES["file"]["name"];
            $post_fileName_array = explode(".", $post_fileName);
            $ext = array_pop($post_fileName_array);
            $filename=implode('.', $post_fileName_array);
            $filename=$this->user_id."_"."contact"."_".time().substr(uniqid(mt_rand(), true), 0, 6).".".$ext;

            $allow = ".csv";
            $allow = str_replace('.', '', $allow);
            $allow = explode(',', $allow);
            if(!in_array(strtolower($ext), $allow)) 
            {
                echo json_encode("Are you kidding???");
                exit;
            }

            move_uploaded_file($_FILES["file"]["tmp_name"], $output_dir.'/'.$filename);
            $ret[]= $filename;
            echo json_encode($filename);
        }
    }


    public function ajax_campaign_import_csv_files()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'GET') exit();
        if($this->session->userdata('user_type') != 'Admin' && count(array_intersect($this->module_access, array('263','264')))==0) exit;

        $ret = array();
        $output_dir = FCPATH."upload/csv";

        if(!file_exists($output_dir))
        {
            mkdir($output_dir,0777);
        }

        if (isset($_FILES["file"])) {

            $error = $_FILES["file"]["error"];

            $post_fileName = $_FILES["file"]["name"];
            $post_fileName_array = explode(".", $post_fileName);
            $ext = array_pop($post_fileName_array);
            $filename = implode('.', $post_fileName_array);
            $filename = $this->user_id."_"."sms"."_".time().substr(uniqid(mt_rand(), true), 0, 6).".".$ext;

            $allow = ".csv";
            $allow = str_replace('.', '', $allow);
            $allow = explode(',', $allow);
            if(!in_array(strtolower($ext), $allow)) 
            {
                echo json_encode("Are you kidding???");
                exit;
            }

            move_uploaded_file($_FILES["file"]["tmp_name"], $output_dir.'/'.$filename);
            $ret[]= $filename;
            echo json_encode($filename);
        }
    }

    public function generating_numbers()
    {
        $this->ajax_check();

        if($this->session->userdata('user_type') != 'Admin' && count(array_intersect($this->module_access, array('263','264')))==0) exit;

        $res = array();

        $filename = strip_tags($this->input->post("fileval",true));

        $csv = FCPATH.'upload/csv/'.$filename;

        if(!file_exists($csv))
        {
            $res['status'] = '0';
            $res['message'] = $this->lang->line("Sorry, file does not exists in the directory.");

        } else{
            $file = file_get_contents($csv);
            
            $file=str_replace(array("\'", "\"","\t","\r"," "), '', $file);
            $file=str_replace(array("\n"), ',', $file);
            $file=trim($file,",");

            $res['status'] = '1';
            $res['message'] = $this->lang->line("your given information has been updated successfully.");
            $res['file'] = $file;
        }

        echo json_encode($res);
    }

    public function delete_uploaded_csv_file()
    {
        if($this->session->userdata('user_type') != 'Admin' && count(array_intersect($this->module_access, array('263','264')))==0) exit;
        if(!$_POST) exit();

        $output_dir = FCPATH."upload/csv/";
        if(isset($_POST["op"]) && $_POST["op"] == "delete" && isset($_POST['name']))
        {
            $fileName = $_POST['name'];
            $fileName = str_replace("..",".",$fileName); //required. if somebody is trying parent folder files
            $filePath = $output_dir. $fileName;
            if (file_exists($filePath))
            {
            unlink($filePath);
            }
        }
    }

    public function import_contact_action_ajax()
    { 
        if($this->session->userdata('user_type') != 'Admin' && count(array_intersect($this->module_access, array('263','264')))==0) exit;

        $user_id = $this->user_id;
        $contact_group = $this->input->post("csv_group_id");

        if(!is_array($contact_group))
            $contact_group = array();

        $contact_groups = implode(",",$contact_group);

        $csv = realpath(FCPATH.'upload/csv/'.$_POST['csv_file']);

        if (!is_readable($csv)) {
            $response['status']="File is not readable.";
        } else {
            $delimiter=',';
            $header = null;
            $data = array();
            if (($handle = fopen($csv, 'r')) !== false) {
                while (($row = fgetcsv($handle, 1000, $delimiter)) !== false) 
                {
                    $data[] =  $row;                        
                }
                fclose($handle);
            }

            $this->db->trans_start();
            $count_insert=0;
            foreach ($data as $row) 
            {
                $insert_data = array();
                $insert_data['user_id'] = $user_id;
                $insert_data['deleted'] = '0';
                $insert_data['first_name'] = isset($row[0]) ? $row[0]:"";
                $insert_data['last_name'] = isset($row[1]) ? $row[1]:"";
                $insert_data['phone_number'] = isset($row[2]) ? $row[2]:"";
                $insert_data['email'] = isset($row[3]) ? $row[3]:"";

                $insert_data['contact_type_id'] = $contact_groups;

                if($insert_data["email"]=="email" || $insert_data["phone_number"]=="phone_number")
                    continue;

                if((isset($insert_data["email"]) && !empty($insert_data["email"])) && $this->basic->is_exist("sms_email_contacts",$where=array("user_id"=>$this->user_id,"email"=>$insert_data["email"])))
                    continue;

                if((isset($insert_data['phone_number']) && !empty($insert_data['phone_number'])) && $this->basic->is_exist("sms_email_contacts",$where=array("user_id"=>$this->user_id,"phone_number"=>$insert_data['phone_number']))) 
                    continue;

                $this->basic->insert_data("sms_email_contacts", $insert_data);
                $count_insert++;
            }
            $this->db->trans_complete();

            if ($this->db->trans_status() === false) {
                $response['status']='Database error occoured. Please try again.';
            } else {
                $response['status'] = 'ok';
                $response['count'] = $count_insert;
            }
        }

        $response['status'] = str_replace("<p>", "", $response['status']);
        $response['status'] = str_replace("</p>", "", $response['status']);

        echo json_encode($response);
    }

    public function ajax_create_new_contact()
    {
        if($this->session->userdata('user_type') != 'Admin' && count(array_intersect($this->module_access, array('263','264')))==0) exit;

        $this->ajax_check();

        $result = array();

        if($_POST)
        {
            $post = $_POST;
            foreach ($post as $key => $value) 
            {
                $$key = $this->input->post($key,TRUE);
            }

            if((isset($contact_email) && !empty($contact_email)) && $this->basic->is_exist("sms_email_contacts",$where=array("user_id"=>$this->user_id,"email"=>$contact_email)))
            {
                $result['status'] = "2";
                $result['msg'] = $this->lang->line("Email Already Exists. Please try with new Email.");

            } else if((isset($phone_number) && !empty($phone_number)) && $this->basic->is_exist("sms_email_contacts",$where=array("user_id"=>$this->user_id,"phone_number"=>$phone_number)))
            {
                $result['status'] = "3";
                $result['msg'] = $this->lang->line("Phone Number Already Exists. Please try with new Phone Number.");
            }
            else
            {
                $userid = $this->user_id;

                $temp = $this->input->post('contact_group_name', true);
                $group = '';
                if (isset($temp)) 
                {
                    $group = implode($temp, ',');
                }

                $contact_type_id = $group;

                $inserted_data = array();
                $inserted_data['first_name'] = trim(strip_tags($first_name));
                $inserted_data['last_name'] = trim(strip_tags($last_name));
                $inserted_data['email'] = trim(strip_tags($contact_email));
                $inserted_data['phone_number'] = trim(strip_tags($phone_number));
                $inserted_data['contact_type_id'] = $contact_type_id;
                $inserted_data['user_id'] = $userid;

                if($this->basic->insert_data("sms_email_contacts",$inserted_data))
                {
                    $result['status'] = "1";
                    $result['msg'] = $this->lang->line("Contact has been added successfully.");
                } else
                {
                    $result['status'] = "0";
                    $result['msg'] = $this->lang->line("Something went wrong, please try once again.");
                }
            }

            echo json_encode($result);

        }
    }

    public function ajax_get_contact_update_info()
    {
        if($this->session->userdata('user_type') != 'Admin' && count(array_intersect($this->module_access, array('263','264')))==0) exit;

        $this->ajax_check();

        $table_id = $this->input->post("table_id");
        $user_id = $this->user_id;

        if($table_id == "0" || $table_id == null) exit;

        $group_info = $this->basic->get_data("sms_email_contact_group",array('where'=>array("user_id"=>$user_id)));
        $contact_details = $this->basic->get_data("sms_email_contacts",array('where'=>array('id'=> $table_id, 'user_id'=> $user_id)));

        $update_contact_type_id = $contact_details[0]['contact_type_id'];
        $ex_update_contact_type_id = explode(',',$update_contact_type_id);


        $form = '<div class="row">
                    <div class="col-12">                    
                        <form action="#" enctype="multipart/form-data" id="contact_update_form" method="post">
                            <input type="hidden" name="table_id" value="'.$table_id.'">
                            <div class="row">
                                <div class="col-12 col-md-6">
                                    <div class="form-group">
                                        <label>'.$this->lang->line('First Name').'</label>
                                        <input type="text" class="form-control" name="first_name" id="updated_first_name" value="'.$contact_details[0]['first_name'].'">
                                    </div>
                                </div>
                                <div class="col-12 col-md-6">
                                    <div class="form-group">
                                        <label>'.$this->lang->line('Last Name').'</label>
                                        <input type="text" class="form-control" name="last_name" id="updated_last_name" value="'.$contact_details[0]['last_name'].'">
                                    </div>
                                </div>
                                <div class="col-12 col-md-6">
                                    <div class="form-group">
                                        <label>'.$this->lang->line('Email').'</label>
                                        <input type="email" class="form-control" name="contact_email" id="updated_contact_email" value="'.$contact_details[0]['email'].'">
                                        
                                    </div>
                                </div>
                                <div class="col-12 col-md-6">
                                    <div class="form-group">
                                        <label>'.$this->lang->line('Phone Number').'</label>
                                        <input type="text" class="form-control" name="phone_number" id="updated_phone_number" value="'.$contact_details[0]['phone_number'].'">
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="form-group">
                                        <label>'.$this->lang->line('Contact Group').'
                                            <a href="#" data-toggle="tooltip" title="'.$this->lang->line("You Can select multiple contact group.").'"><i class="fas fa-info-circle"></i></a>
                                        </label>
                                        <select name="contact_group_name[]" id="updated_contact_group_name" multiple class="form-control select2" style="width:100%;">';
                                            foreach($group_info as $key => $val)
                                            {
                                                $comparing_group_id = $val['id'];

                                                if(in_array($comparing_group_id, $ex_update_contact_type_id))
                                                {
                                                    $form .='<option value="'.$comparing_group_id.'" selected>'.$val['type'].'</option>';
                                                } else
                                                {
                                                    $form .='<option value="'.$comparing_group_id.'">'.$val['type'].'</option>';
                                                }
                                            }
                                            
        $form .='</select>
                        </div>
                    </div>
                </div>
                </form>
            </div>
        </div>
        <script>$("#updated_contact_group_name").select2()';

        echo $form;
    }

    public function ajax_update_contact()
    {
        if($this->session->userdata('user_type') != 'Admin' && count(array_intersect($this->module_access, array('263','264')))==0) exit;

        $this->ajax_check();

        $table_id = $this->input->post("table_id");

        $result = array();

        if($_POST)
        {
            $post = $_POST;
            foreach ($post as $key => $value) 
            {
                $$key = $this->input->post($key,TRUE);
            }

            $oldData = $this->basic->get_data("sms_email_contacts", array("where"=>array("id"=>$table_id,"user_id"=>$this->user_id)),array("phone_number","email"));


            if($oldData[0]['email'] != $contact_email && $oldData[0]['phone_number'] != $phone_number)
            {
                if($this->basic->is_exist("sms_email_contacts",$where=array("user_id"=>$this->user_id,"phone_number"=>$phone_number,"email"=>$contact_email)))
                {
                    $result['status'] = "4";
                    $result['msg'] = $this->lang->line("Email and Phone Number Already Exists. Please try with different Email/Phone Number.");
                    echo json_encode($result);
                    exit;
                }
            }
            
            if(!empty($contact_email) && ($oldData[0]['email'] != $contact_email))
            {
                if((isset($contact_email) && !empty($contact_email)) && $this->basic->is_exist("sms_email_contacts",$where=array("user_id"=>$this->user_id,"email"=>$contact_email)))
                {
                    $result['status'] = "2";
                    $result['msg'] = $this->lang->line("Email Already Exists. Please try with new Email.");
                    echo json_encode($result);
                    exit;
                } 
            }

            if(!empty($phone_number) && ($oldData[0]['phone_number'] != $phone_number))
            {
                if((isset($phone_number) && !empty($phone_number)) && $this->basic->is_exist("sms_email_contacts",$where=array("user_id"=>$this->user_id,"phone_number"=>$phone_number)))
                {
                    $result['status'] = "3";
                    $result['msg'] = $this->lang->line("Phone Number Already Exists. Please try with new Phone Number.");
                    echo json_encode($result);
                    exit;
                }
            } 

            $temp = $this->input->post('contact_group_name', true);
            $group = '';
            if (isset($temp)) 
            {
                $group = implode($temp, ',');
            }

            $contact_type_id = $group;

            $inserted_data = array();
            $updated_data['first_name'] = trim(strip_tags($first_name));
            $updated_data['last_name'] = trim(strip_tags($last_name));
            $updated_data['email'] = trim(strip_tags($contact_email));
            $updated_data['phone_number'] = trim(strip_tags($phone_number));
            $updated_data['contact_type_id'] = $contact_type_id;
            $updated_data['user_id'] = $this->user_id;

            $where = array("id"=>$table_id,'user_id'=>$this->user_id);

            if($this->basic->update_data("sms_email_contacts",$where,$updated_data))
            {
                $result['status'] = "1";
                $result['msg'] = $this->lang->line("Contact has been updated successfully.");
            } else
            {
                $result['status'] = "0";
                $result['msg'] = $this->lang->line("Something went wrong, please try once again.");
            }

            echo json_encode($result);

        }
    }

    public function delete_contact()
    {
        if($this->session->userdata('user_type') != 'Admin' && count(array_intersect($this->module_access, array('263','264')))==0) exit;

        $this->ajax_check();
        $table_id = $this->input->post("table_id");
        if($table_id == "0" || $table_id == null) exit;

        if($this->basic->delete_data("sms_email_contacts", array("id"=>$table_id,'user_id'=>$this->user_id)))
        {
            echo "1";
        } else
        {
            echo "0";
        }

    }
    // End of phonebook Section


    // =============================== Email API SECTION ==================================
    public function smtp_config()
    {
        if($this->session->userdata('user_type') != 'Admin' && !in_array(263,$this->module_access)) redirect('home/login_page', 'location');

        $data['body'] = 'sms_email_manager/email/email_api_config/smtp_config';

        $data['page_title'] = $this->lang->line('SMTP API');
        $this->_viewcontroller($data);
    }

    public function smtp_config_data()
    {
        if($this->session->userdata('user_type') != 'Admin' && !in_array(263,$this->module_access)) exit;

        $this->ajax_check();

        $search_value = $_POST['search']['value'];
        $display_columns = array("#",'id','email_address','smtp_host','smtp_port','smtp_user','smtp_password','smtp_type','status','actions');
        $search_columns = array('email_address','smtp_host','smtp_user','smtp_type');

        $page = isset($_POST['page']) ? intval($_POST['page']) : 1;
        $start = isset($_POST['start']) ? intval($_POST['start']) : 0;
        $limit = isset($_POST['length']) ? intval($_POST['length']) : 10;
        $sort_index = isset($_POST['order'][0]['column']) ? strval($_POST['order'][0]['column']) : 1;
        $sort = isset($display_columns[$sort_index]) ? $display_columns[$sort_index] : 'id';
        $order = isset($_POST['order'][0]['dir']) ? strval($_POST['order'][0]['dir']) : 'desc';
        $order_by=$sort." ".$order;

        $where_custom = '';
        $where_custom = "user_id = ".$this->user_id;

        if ($search_value != '') 
        {
            foreach ($search_columns as $key => $value) 
            $temp[] = $value." LIKE "."'%$search_value%'";
            $imp = implode(" OR ", $temp);
            $where_custom .=" AND (".$imp.") ";
        }

        $table = "email_smtp_config";
        $this->db->where($where_custom);
        $info = $this->basic->get_data($table,$where='',$select='',$join='',$limit,$start,$order_by,$group_by='');

        $this->db->where($where_custom);
        $total_rows_array = $this->basic->count_row($table,$where='',$count="id",$join="",$group_by='');
        $total_result=$total_rows_array[0]['total_rows'];

        for ($i=0; $i < count($info) ; $i++) 
        { 
            $status = $info[$i]["status"];
            if($status=='1') $info[$i]["status"] = "<i title ='".$this->lang->line('Active')."'class='status-icon fas fa-toggle-on text-primary'></i>";
            else $info[$i]["status"] = "<i title ='".$this->lang->line('Inactive')."'class='status-icon fas fa-toggle-off gray'></i>";

            $info[$i]['actions'] = "<div style='min-width:100px'><a href='#' title='".$this->lang->line("Edit")."' class='btn btn-circle btn-outline-warning edit_smtp' table_id='".$info[$i]['id']."'><i class='fa fa-edit'></i></a>&nbsp;&nbsp;";

            $info[$i]['actions'] .= "<a href='#' title='".$this->lang->line("Delete")."' class='btn btn-circle btn-outline-danger delete_smtp' table_id='".$info[$i]['id']."'><i class='fa fa-trash-alt'></i></a></div>
                <script>$('[data-toggle=\"tooltip\"]').tooltip();</script>";
        }


        $data['draw'] = (int)$_POST['draw'] + 1;
        $data['recordsTotal'] = $total_result;
        $data['recordsFiltered'] = $total_result;
        $data['data'] = convertDataTableResult($info, $display_columns ,$start,$primary_key="id");

        echo json_encode($data);
        
    }

    public function ajax_save_smtp_api()
    {
        if($this->session->userdata('user_type') != 'Admin' && !in_array(263,$this->module_access)) exit;

        $this->ajax_check();

        $save_data = array();
        $ret = array();

        if($_POST)
        {
            $post = $_POST;
            foreach ($post as $key => $value) 
            {
                $$key = $this->input->post($key,TRUE);
            }

            $smtp_status = $this->input->post("smtp_status",true);
            if($smtp_status == "") $smtp_status = "0";

            $save_data['user_id']       = $this->user_id;
            $save_data['email_address'] = trim(strip_tags($smtp_email));
            $save_data['smtp_host']     = trim(strip_tags($smtp_host));
            $save_data['smtp_port']     = trim(strip_tags($smtp_port));
            $save_data['smtp_user']     = trim(strip_tags($smtp_username));
            $save_data['smtp_password'] = trim(strip_tags($smtp_password));
            $save_data['smtp_type']     = trim(strip_tags($smtp_type));
            $save_data['status']        = trim(strip_tags($smtp_status));
            if($this->basic->insert_data("email_smtp_config",$save_data))
            {
                $ret['status'] = '1';
                $ret['msg'] = $this->lang->line("SMTP API Information has been added successfully.");
            } else
            {
                $ret['status'] = '0';
                $ret['msg'] = $this->lang->line("Something went wrong, please try once again.");
            }

            echo json_encode($ret);
        }

    }

    public function ajax_get_smtp_api_info()
    {
        if($this->session->userdata('user_type') != 'Admin' && !in_array(263,$this->module_access)) exit;

        $this->ajax_check();

        $user_id = $this->user_id;
        $table_id = $this->input->post("table_id",true);
        if($table_id == "0" || $table_id == "") exit;

        $smtp_api_info = $this->basic->get_data("email_smtp_config",array("where"=>array("user_id"=>$user_id,"id"=>$table_id)));

        $smtp_email = $smtp_api_info[0]['email_address'];
        $smtp_host  = $smtp_api_info[0]['smtp_host'];
        $smtp_port  = $smtp_api_info[0]['smtp_port'];
        $smtp_user  = $smtp_api_info[0]['smtp_user'];
        $smtp_pass  = $smtp_api_info[0]['smtp_password'];
        $smtp_type  = $smtp_api_info[0]['smtp_type'];
        $status     = $smtp_api_info[0]['status'];

        if($smtp_type == "Default") $default_selected = "selected";
        else $default_selected = ""; 
        if($smtp_type == "tls") $tls_selected = "selected";
        else $tls_selected = ""; 
        if($smtp_type == "ssl") $ssl_selected = "selected";
        else $ssl_selected = ""; 

        if($status == "1") $status = "checked";
        else $status = "";

        $update_form = '
        <div class="row">
            <div class="col-12">
                <form action="#" method="POST" id="smtp_api_update_form">
                    <input type="hidden" name="table_id" id="table_id" value="'.$table_id.'">
                    <div class="row">
                        <div class="col-12 col-md-6">
                            <div class="form-group">
                                <label>'.$this->lang->line('Email Address').'</label>
                                <input type="text" class="form-control" id="updated_smtp_email" name="smtp_email" value="'.$smtp_email.'">
                            </div>
                        </div>
                        <div class="col-12 col-md-6">
                            <div class="form-group">
                                <label>'.$this->lang->line('SMTP Host').'</label>
                                <input type="text" class="form-control" id="updated_smtp_host" name="smtp_host" value="'.$smtp_host.'">
                            </div>
                        </div>
                        <div class="col-12 col-md-6">
                            <div class="form-group">
                                <label>'.$this->lang->line('SMTP Port').'</label>
                                <input type="text" class="form-control" id="updated_smtp_port" name="smtp_port" value="'.$smtp_port.'">
                            </div>
                        </div>
                        <div class="col-12 col-md-6">
                            <div class="form-group">
                                <label>'.$this->lang->line('SMTP Username').'</label>
                                <input type="text" class="form-control" id="updated_smtp_username" name="smtp_username" value="'.$smtp_user.'">
                            </div>
                        </div>
                        <div class="col-12 col-md-6">
                            <div class="form-group">
                                <label>'.$this->lang->line('SMTP Password').'</label>
                                <input type="text" class="form-control" id="updated_smtp_password" name="smtp_password" value="'.$smtp_pass.'">
                            </div>
                        </div>
                        <div class="col-12 col-md-6">
                            <div class="form-group">
                                <label>'.$this->lang->line('SMTP Type').'</label>
                                <select class="form-control select2" id="updated_smtp_type" name="smtp_type" style="width:100%;">
                                    <option value="Default" '.$default_selected.'>'.$this->lang->line('Default').'</option>
                                    <option value="tls" '.$tls_selected.'>'.$this->lang->line('tls').'</option>
                                    <option value="ssl" '.$ssl_selected.'>'.$this->lang->line('ssl').'</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-12 col-md-6">
                            <div class="form-group">
                                <label>'.$this->lang->line('Status').'</label><br>
                                <label class="custom-switch">
                                    <input type="checkbox" name="smtp_status" value="1" id="updated_smtp_status" class="custom-switch-input" '.$status.'>
                                    <span class="custom-switch-indicator"></span>
                                    <span class="custom-switch-description">'.$this->lang->line('Active').'</span>
                                </label>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="col-12"><br>
                <button type="button" class="btn btn-primary btn-lg" id="update_smtp"><i class="fas fa-edit"></i> '.$this->lang->line('Update').'</button>
                <button type="button" class="btn btn-light btn-lg float-right" data-dismiss="modal"><i class="fas fa-times"></i> '.$this->lang->line('Close').'</button>
            </div>
        </div><script>$("#updated_smtp_type").select2();</script>';

        echo $update_form;
    }


    public function ajax_update_smtp_api()
    {
        if($this->session->userdata('user_type') != 'Admin' && !in_array(263,$this->module_access)) exit;

        $this->ajax_check();

        $save_data = array();
        $ret = array();

        $table_id = $this->input->post("table_id");

        if($_POST)
        {
            $post = $_POST;
            foreach ($post as $key => $value) 
            {
                $$key = $this->input->post($key,TRUE);
            }

            $smtp_status = $this->input->post("smtp_status",true);
            if($smtp_status == "") $smtp_status = "0";

            $save_data['user_id']       = $this->user_id;
            $save_data['email_address'] = trim(strip_tags($smtp_email));
            $save_data['smtp_host']     = trim(strip_tags($smtp_host));
            $save_data['smtp_port']     = trim(strip_tags($smtp_port));
            $save_data['smtp_user']     = trim(strip_tags($smtp_username));
            $save_data['smtp_password'] = trim(strip_tags($smtp_password));
            $save_data['smtp_type']     = trim(strip_tags($smtp_type));
            $save_data['status']        = trim(strip_tags($smtp_status));
            if($this->basic->update_data("email_smtp_config",array("user_id"=>$this->user_id,'id'=>$table_id),$save_data))
            {
                $ret['status'] = '1';
                $ret['msg'] = $this->lang->line("SMTP API Information has been updated successfully.");
            } else
            {
                $ret['status'] = '0';
                $ret['msg'] = $this->lang->line("Something went wrong, please try once again.");
            }

            echo json_encode($ret);
        }
    }

    public function delete_smtp_api()
    {
        if($this->session->userdata('user_type') != 'Admin' && !in_array(263,$this->module_access)) exit;

        $this->ajax_check();

        $table_id = $this->input->post("table_id",true);
        if($table_id == "0" || $table_id == "") exit;

        if($this->basic->delete_data("email_smtp_config",array("id"=>$table_id)))
        {
            echo "1";

        } else
        {
            echo "0";
        }
    }

    // mailgun section started
    public function mailgun_api_config()
    {
        if($this->session->userdata('user_type') != 'Admin' && !in_array(263,$this->module_access)) redirect('home/login_page', 'location');

        $data['body'] = 'sms_email_manager/email/email_api_config/mailgun_config';
        $data['page_title'] = $this->lang->line('Mailgun API');
        $this->_viewcontroller($data);
    }

    public function mailgun_config_data()
    {
        if($this->session->userdata('user_type') != 'Admin' && !in_array(263,$this->module_access)) exit;

        $this->ajax_check();

        $search_value = $_POST['search']['value'];
        $display_columns = array("#",'id','email_address','domain_name','api_key','status','actions');
        $search_columns = array('email_address','domain_name');

        $page = isset($_POST['page']) ? intval($_POST['page']) : 1;
        $start = isset($_POST['start']) ? intval($_POST['start']) : 0;
        $limit = isset($_POST['length']) ? intval($_POST['length']) : 10;
        $sort_index = isset($_POST['order'][0]['column']) ? strval($_POST['order'][0]['column']) : 1;
        $sort = isset($display_columns[$sort_index]) ? $display_columns[$sort_index] : 'id';
        $order = isset($_POST['order'][0]['dir']) ? strval($_POST['order'][0]['dir']) : 'desc';
        $order_by=$sort." ".$order;

        $where_custom = '';
        $where_custom = "user_id = ".$this->user_id;

        if ($search_value != '') 
        {
            foreach ($search_columns as $key => $value) 
            $temp[] = $value." LIKE "."'%$search_value%'";
            $imp = implode(" OR ", $temp);
            $where_custom .=" AND (".$imp.") ";
        }

        $table = "email_mailgun_config";
        $this->db->where($where_custom);
        $info = $this->basic->get_data($table,$where='',$select='',$join='',$limit,$start,$order_by,$group_by='');

        $this->db->where($where_custom);
        $total_rows_array = $this->basic->count_row($table,$where='',$count="id",$join="",$group_by='');
        $total_result=$total_rows_array[0]['total_rows'];

        for ($i=0; $i < count($info) ; $i++) 
        { 
            $status = $info[$i]["status"];
            if($status=='1') $info[$i]["status"] = "<i title ='".$this->lang->line('Active')."'class='status-icon fas fa-toggle-on text-primary'></i>";
            else $info[$i]["status"] = "<i title ='".$this->lang->line('Inactive')."'class='status-icon fas fa-toggle-off gray'></i>";

            $info[$i]['actions'] = "<div style='min-width:100px'><a href='#' title='".$this->lang->line("Edit")."' class='btn btn-circle btn-outline-warning edit_mailgun_api' table_id='".$info[$i]['id']."'><i class='fa fa-edit'></i></a>&nbsp;&nbsp;";

            $info[$i]['actions'] .= "<a href='#' title='".$this->lang->line("Delete")."' class='btn btn-circle btn-outline-danger delete_mailgun_api' table_id='".$info[$i]['id']."'><i class='fa fa-trash-alt'></i></a></div>
                <script>$('[data-toggle=\"tooltip\"]').tooltip();</script>";
        }


        $data['draw'] = (int)$_POST['draw'] + 1;
        $data['recordsTotal'] = $total_result;
        $data['recordsFiltered'] = $total_result;
        $data['data'] = convertDataTableResult($info, $display_columns ,$start,$primary_key="id");

        echo json_encode($data);   
    }

    public function ajax_mailgun_api_save()
    {
        if($this->session->userdata('user_type') != 'Admin' && !in_array(263,$this->module_access)) exit;

        $this->ajax_check();

        $save_data = array();
        $ret = array();

        if($_POST)
        {
            $post = $_POST;
            foreach ($post as $key => $value) 
            {
                $$key = $this->input->post($key,TRUE);
            }

            $mailgun_status = $this->input->post("mailgun_status",true);
            if($mailgun_status == "") $mailgun_status = "0";

            $save_data['user_id']       = $this->user_id;
            $save_data['email_address'] = trim(strip_tags($mailgun_email));
            $save_data['domain_name']   = trim(strip_tags($mailgun_domain));
            $save_data['api_key']       = trim(strip_tags($mailgun_api_key));
            $save_data['status']        = trim(strip_tags($mailgun_status));

            if($this->basic->insert_data("email_mailgun_config",$save_data))
            {
                $ret['status'] = '1';
                $ret['msg'] = $this->lang->line("Mailgun API Information has been added successfully.");
            } else
            {
                $ret['status'] = '0';
                $ret['msg'] = $this->lang->line("Something went wrong, please try once again.");
            }

            echo json_encode($ret);
        }
    }

    public function ajax_get_mailgun_api_info()
    {
        if($this->session->userdata('user_type') != 'Admin' && !in_array(263,$this->module_access)) exit;

        $this->ajax_check();

        $user_id  = $this->user_id;
        $table_id = $this->input->post("table_id");
        if($table_id == "0" || $table_id == "") exit;

        $table = "email_mailgun_config";
        $where['where'] = array("id"=>$table_id,"user_id"=>$user_id);
        $mailgun_api_info = $this->basic->get_data($table,$where);

        $email   = $mailgun_api_info[0]['email_address'];
        $domain  = $mailgun_api_info[0]['domain_name'];
        $api_key = $mailgun_api_info[0]['api_key'];
        $status  = $mailgun_api_info[0]['status'];

        if($status == "1") $status_checked = "checked";
        else $status_checked = "";

        $updated_form ='
        <div class="row">
            <div class="col-12">
                <form action="#" method="POST" id="update_mailgun_api_form">
                    <input type="hidden" name="table_id" id="table_id" value="'.$table_id.'">
                    <div class="row">
                        <div class="col-12">
                            <div class="form-group">
                                <label>'.$this->lang->line('Email Address').'</label>
                                <input type="text" class="form-control" id="updated_mailgun_email" name="mailgun_email" value="'.$email.'">
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="form-group">
                                <label>'.$this->lang->line('Domain Name').'</label>
                                <input type="text" class="form-control" id="updated_mailgun_domain" name="mailgun_domain" value="'.$domain.'">
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="form-group">
                                <label>'.$this->lang->line('API Key').'</label>
                                <input type="text" class="form-control" id="updated_mailgun_api_key" name="mailgun_api_key" value="'.$api_key.'">
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="form-group">
                                <label>'.$this->lang->line('Status').'</label><br>
                                <label class="custom-switch">
                                    <input type="checkbox" name="mailgun_status" value="1" id="updated_mailgun_status" class="custom-switch-input" '.$status_checked.'>
                                    <span class="custom-switch-indicator"></span>
                                    <span class="custom-switch-description">'.$this->lang->line('Active').'</span>
                                </label>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="col-12"><br>
                <button type="button" class="btn btn-primary btn-lg" id="update_mailgun"><i class="fas fa-edit"></i> '.$this->lang->line('Update').'</button>
                <button type="button" class="btn btn-light btn-lg float-right" data-dismiss="modal"><i class="fas fa-times"></i> '.$this->lang->line('Close').'</button>
            </div>
        </div>';

        echo $updated_form;
    }

    public function ajax_update_mailgun_api_info()
    {
        if($this->session->userdata('user_type') != 'Admin' && !in_array(263,$this->module_access)) exit;

        $this->ajax_check();

        $save_data = array();
        $ret = array();

        $table_id = $this->input->post("table_id");

        if($_POST)
        {
            $post = $_POST;
            foreach ($post as $key => $value) 
            {
                $$key = $this->input->post($key,TRUE);
            }

            $mailgun_status = $this->input->post("mailgun_status",true);
            if($mailgun_status == "") $mailgun_status = "0";

            $save_data['user_id']       = $this->user_id;
            $save_data['email_address'] = trim(strip_tags($mailgun_email));
            $save_data['domain_name']   = trim(strip_tags($mailgun_domain));
            $save_data['api_key']       = trim(strip_tags($mailgun_api_key));
            $save_data['status']        = trim(strip_tags($mailgun_status));

            if($this->basic->update_data("email_mailgun_config",array("id"=>$table_id,"user_id"=>$this->user_id),$save_data))
            {
                $ret['status'] = '1';
                $ret['msg'] = $this->lang->line("Mailgun API Information has been updated successfully.");
            } else
            {
                $ret['status'] = '0';
                $ret['msg'] = $this->lang->line("Something went wrong, please try once again.");
            }

            echo json_encode($ret);
        }
    }

    public function delete_mailgun_api()
    {
        if($this->session->userdata('user_type') != 'Admin' && !in_array(263,$this->module_access)) exit;

        $this->ajax_check();

        $table_id = $this->input->post("table_id");
        if($table_id == "0" || $table_id == "") exit;

        if($this->basic->delete_data("email_mailgun_config",array("id"=>$table_id,"user_id"=>$this->user_id)))
        {
            echo "1";
        } else
        {
            echo "0";
        }
    }

    //mailgun section
    public function mandrill_api_config()
    {
        if($this->session->userdata('user_type') != 'Admin' && !in_array(263,$this->module_access)) redirect('home/login_page', 'location');

        $data['body'] = 'sms_email_manager/email/email_api_config/mandrill_config';
        $data['page_title'] = $this->lang->line('Mandrill API');
        $this->_viewcontroller($data);
    }

    public function mandrill_config_data()
    {
        if($this->session->userdata('user_type') != 'Admin' && !in_array(263,$this->module_access)) exit;

        $this->ajax_check();

        $search_value = $_POST['search']['value'];
        $display_columns = array("#",'id','your_name','email_address','api_key','status','actions');
        $search_columns = array('email_address','your_name','api_key');

        $page = isset($_POST['page']) ? intval($_POST['page']) : 1;
        $start = isset($_POST['start']) ? intval($_POST['start']) : 0;
        $limit = isset($_POST['length']) ? intval($_POST['length']) : 10;
        $sort_index = isset($_POST['order'][0]['column']) ? strval($_POST['order'][0]['column']) : 1;
        $sort = isset($display_columns[$sort_index]) ? $display_columns[$sort_index] : 'id';
        $order = isset($_POST['order'][0]['dir']) ? strval($_POST['order'][0]['dir']) : 'desc';
        $order_by=$sort." ".$order;

        $where_custom = '';
        $where_custom = "user_id = ".$this->user_id;

        if ($search_value != '') 
        {
            foreach ($search_columns as $key => $value) 
            $temp[] = $value." LIKE "."'%$search_value%'";
            $imp = implode(" OR ", $temp);
            $where_custom .=" AND (".$imp.") ";
        }

        $table = "email_mandrill_config";
        $this->db->where($where_custom);
        $info = $this->basic->get_data($table,$where='',$select='',$join='',$limit,$start,$order_by,$group_by='');

        $this->db->where($where_custom);
        $total_rows_array = $this->basic->count_row($table,$where='',$count="id",$join="",$group_by='');
        $total_result=$total_rows_array[0]['total_rows'];

        for ($i=0; $i < count($info) ; $i++) 
        { 
            $status = $info[$i]["status"];
            if($status=='1') $info[$i]["status"] = "<i title ='".$this->lang->line('Active')."'class='status-icon fas fa-toggle-on text-primary'></i>";
            else $info[$i]["status"] = "<i title ='".$this->lang->line('Inactive')."'class='status-icon fas fa-toggle-off gray'></i>";

            $info[$i]['actions'] = "<div style='min-width:100px'><a href='#' title='".$this->lang->line("Edit")."' class='btn btn-circle btn-outline-warning edit_mandrill_api' table_id='".$info[$i]['id']."'><i class='fa fa-edit'></i></a>&nbsp;&nbsp;";

            $info[$i]['actions'] .= "<a href='#' title='".$this->lang->line("Delete")."' class='btn btn-circle btn-outline-danger delete_mandrill_api' table_id='".$info[$i]['id']."'><i class='fa fa-trash-alt'></i></a></div>
                <script>$('[data-toggle=\"tooltip\"]').tooltip();</script>";
        }


        $data['draw'] = (int)$_POST['draw'] + 1;
        $data['recordsTotal'] = $total_result;
        $data['recordsFiltered'] = $total_result;
        $data['data'] = convertDataTableResult($info, $display_columns ,$start,$primary_key="id");

        echo json_encode($data);
    }

    public function ajax_mandrill_api_save()
    {
        if($this->session->userdata('user_type') != 'Admin' && !in_array(263,$this->module_access)) exit;

        $this->ajax_check();

        $save_data = array();
        $ret = array();

        if($_POST)
        {
            $post = $_POST;
            foreach ($post as $key => $value) 
            {
                $$key = $this->input->post($key,TRUE);
            }

            $mandrill_status = $this->input->post("mandrill_status",true);
            if($mandrill_status == "") $mandrill_status = "0";

            $save_data['user_id']       = $this->user_id;
            $save_data['your_name']     = trim(strip_tags($mandrill_name));
            $save_data['email_address'] = trim(strip_tags($mandrill_email));
            $save_data['api_key']       = trim(strip_tags($mandrill_api_key));
            $save_data['status']        = trim(strip_tags($mandrill_status));

            if($this->basic->insert_data("email_mandrill_config",$save_data))
            {
                $ret['status'] = '1';
                $ret['msg'] = $this->lang->line("Mandrill API Information has been added successfully.");
            } else
            {
                $ret['status'] = '0';
                $ret['msg'] = $this->lang->line("Something went wrong, please try once again.");
            }

            echo json_encode($ret);
        }
    }

    public function ajax_get_mandrill_api_info()
    {
        if($this->session->userdata('user_type') != 'Admin' && !in_array(263,$this->module_access)) exit;

        $this->ajax_check();

        $user_id  = $this->user_id;
        $table_id = $this->input->post("table_id");
        if($table_id == "0" || $table_id == "") exit;

        $table = "email_mandrill_config";
        $where['where'] = array("id"=>$table_id,"user_id"=>$user_id);
        $mailgun_api_info = $this->basic->get_data($table,$where);

        $name    = $mailgun_api_info[0]['your_name'];
        $email   = $mailgun_api_info[0]['email_address'];
        $api_key = $mailgun_api_info[0]['api_key'];
        $status  = $mailgun_api_info[0]['status'];

        if($status == "1") $status_checked = "checked";
        else $status_checked = "";

        $updated_form ='
        <div class="row">
            <div class="col-12">
                <form action="#" method="POST" id="update_mandrill_api">
                    <input type="hidden" name="table_id" id="table_id" value="'.$table_id.'">
                    <div class="row">
                        <div class="col-12">
                            <div class="form-group">
                                <label>'.$this->lang->line('Your Name').'</label>
                                <input type="text" class="form-control" id="updated_mandrill_name" name="mandrill_name" value="'.$name.'">
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="form-group">
                                <label>'.$this->lang->line('Email Address').'</label>
                                <input type="text" class="form-control" id="updated_mandrill_email" name="mandrill_email" value="'.$email.'">
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="form-group">
                                <label>'.$this->lang->line('API Key').'</label>
                                <input type="text" class="form-control" id="updated_mandrill_api_key" name="mandrill_api_key" value="'.$api_key.'">
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="form-group">
                                <label>'.$this->lang->line('Status').'</label><br>
                                <label class="custom-switch">
                                    <input type="checkbox" name="mandrill_status" value="1" id="updated_mandrill_status" class="custom-switch-input" '.$status_checked.'>
                                    <span class="custom-switch-indicator"></span>
                                    <span class="custom-switch-description">'.$this->lang->line('Active').'</span>
                                </label>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="col-12"><br>
                <button type="button" class="btn btn-primary btn-lg" id="update_mandrill"><i class="fas fa-edit"></i> '.$this->lang->line('Update').'</button>
                <button type="button" class="btn btn-light btn-lg float-right" data-dismiss="modal"><i class="fas fa-times"></i> '.$this->lang->line('Close').'</button>
            </div>
        </div>';

        echo $updated_form;
    }

    public function ajax_update_mandrill_api_info()
    {
        if($this->session->userdata('user_type') != 'Admin' && !in_array(263,$this->module_access)) exit;

        $this->ajax_check();

        $save_data = array();
        $ret = array();

        $table_id = $this->input->post("table_id");

        if($_POST)
        {
            $post = $_POST;
            foreach ($post as $key => $value) 
            {
                $$key = $this->input->post($key,TRUE);
            }

            $mandrill_status = $this->input->post("mandrill_status",true);
            if($mandrill_status == "") $mandrill_status = "0";

            $save_data['user_id']       = $this->user_id;
            $save_data['your_name']     = trim(strip_tags($mandrill_name));
            $save_data['email_address'] = trim(strip_tags($mandrill_email));
            $save_data['api_key']       = trim(strip_tags($mandrill_api_key));
            $save_data['status']        = trim(strip_tags($mandrill_status));

            if($this->basic->update_data("email_mandrill_config",array('id'=>$table_id,'user_id'=>$this->user_id),$save_data))
            {
                $ret['status'] = '1';
                $ret['msg'] = $this->lang->line("Mandrill API Information has been updated successfully.");
            } else
            {
                $ret['status'] = '0';
                $ret['msg'] = $this->lang->line("Something went wrong, please try once again.");
            }

            echo json_encode($ret);
        }
    }

    public function delete_mandrill_api()
    {
        $this->ajax_check();
        if($this->session->userdata('user_type') != 'Admin' && !in_array(263,$this->module_access)) exit;

        $table_id = $this->input->post("table_id");
        if($table_id == "0" || $table_id == "") exit;

        if($this->basic->delete_data("email_mandrill_config",array("id"=>$table_id,"user_id"=>$this->user_id)))
        {
            echo "1";
        } else
        {
            echo "0";
        }
    }


    // Sendgrid section started
    public function sendgrid_api_config()
    {
        if($this->session->userdata('user_type') != 'Admin' && !in_array(263,$this->module_access)) redirect('home/login_page', 'location');

        $data['body'] = 'sms_email_manager/email/email_api_config/sendgrid_config';
        $data['page_title'] = $this->lang->line('Sendgrid API');
        $this->_viewcontroller($data);
    }

    public function sendgrid_config_data()
    {
        $this->ajax_check();

        if($this->session->userdata('user_type') != 'Admin' && !in_array(263,$this->module_access)) exit;

        $search_value = $_POST['search']['value'];
        $display_columns = array("#",'id','email_address','username','password','status','actions');
        $search_columns = array('email_address','username');

        $page = isset($_POST['page']) ? intval($_POST['page']) : 1;
        $start = isset($_POST['start']) ? intval($_POST['start']) : 0;
        $limit = isset($_POST['length']) ? intval($_POST['length']) : 10;
        $sort_index = isset($_POST['order'][0]['column']) ? strval($_POST['order'][0]['column']) : 1;
        $sort = isset($display_columns[$sort_index]) ? $display_columns[$sort_index] : 'id';
        $order = isset($_POST['order'][0]['dir']) ? strval($_POST['order'][0]['dir']) : 'desc';
        $order_by=$sort." ".$order;

        $where_custom = '';
        $where_custom = "user_id = ".$this->user_id;

        if ($search_value != '') 
        {
            foreach ($search_columns as $key => $value) 
            $temp[] = $value." LIKE "."'%$search_value%'";
            $imp = implode(" OR ", $temp);
            $where_custom .=" AND (".$imp.") ";
        }

        $table = "email_sendgrid_config";
        $this->db->where($where_custom);
        $info = $this->basic->get_data($table,$where='',$select='',$join='',$limit,$start,$order_by,$group_by='');

        $this->db->where($where_custom);
        $total_rows_array = $this->basic->count_row($table,$where='',$count="id",$join="",$group_by='');
        $total_result=$total_rows_array[0]['total_rows'];

        for ($i=0; $i < count($info) ; $i++) 
        { 
            $status = $info[$i]["status"];
            if($status=='1') $info[$i]["status"] = "<i title ='".$this->lang->line('Active')."'class='status-icon fas fa-toggle-on text-primary'></i>";
            else $info[$i]["status"] = "<i title ='".$this->lang->line('Inactive')."'class='status-icon fas fa-toggle-off gray'></i>";

            $info[$i]['actions'] = "<div style='min-width:100px'><a href='#' title='".$this->lang->line("Edit")."' class='btn btn-circle btn-outline-warning edit_sendgrid_api' table_id='".$info[$i]['id']."'><i class='fa fa-edit'></i></a>&nbsp;&nbsp;";

            $info[$i]['actions'] .= "<a href='#' title='".$this->lang->line("Delete")."' class='btn btn-circle btn-outline-danger delete_sendgrid_api' table_id='".$info[$i]['id']."'><i class='fa fa-trash-alt'></i></a></div>
                <script>$('[data-toggle=\"tooltip\"]').tooltip();</script>";
        }

        $data['draw'] = (int)$_POST['draw'] + 1;
        $data['recordsTotal'] = $total_result;
        $data['recordsFiltered'] = $total_result;
        $data['data'] = convertDataTableResult($info, $display_columns ,$start,$primary_key="id");

        echo json_encode($data);
    }

    public function ajax_sendgrid_api_save()
    {
        if($this->session->userdata('user_type') != 'Admin' && !in_array(263,$this->module_access)) exit;

        $this->ajax_check();

        $save_data = array();
        $ret = array();

        if($_POST)
        {
            $post = $_POST;
            foreach ($post as $key => $value) 
            {
                $$key = $this->input->post($key,TRUE);
            }

            $sendgrid_status = $this->input->post("sendgrid_status",true);
            if($sendgrid_status == "") $sendgrid_status = "0";

            $save_data['user_id']       = $this->user_id;
            $save_data['email_address'] = trim(strip_tags($sendgrid_email));
            $save_data['username']      = trim(strip_tags($sendgrid_username));
            $save_data['password']      = trim(strip_tags($sendgrid_password));
            $save_data['status']        = trim(strip_tags($sendgrid_status));

            if($this->basic->insert_data("email_sendgrid_config",$save_data))
            {
                $ret['status'] = '1';
                $ret['msg'] = $this->lang->line("Sendgrid API Information has been added successfully.");
            } else
            {
                $ret['status'] = '0';
                $ret['msg'] = $this->lang->line("Something went wrong, please try once again.");
            }

            echo json_encode($ret);
        }

    }

    public function ajax_get_sendgrid_api_info()
    {
        if($this->session->userdata('user_type') != 'Admin' && !in_array(263,$this->module_access)) exit;

        $this->ajax_check();

        $user_id  = $this->user_id;
        $table_id = $this->input->post("table_id");
        if($table_id == "0" || $table_id == "") exit;

        $table = "email_sendgrid_config";
        $where['where'] = array("id"=>$table_id,"user_id"=>$user_id);
        $sendgrid_api_info = $this->basic->get_data($table,$where);

        $email    = $sendgrid_api_info[0]['email_address'];
        $username = $sendgrid_api_info[0]['username'];
        $password = $sendgrid_api_info[0]['password'];
        $status   = $sendgrid_api_info[0]['status'];

        if($status == "1") $status_checked = "checked";
        else $status_checked = "";

        $updated_forms = '
        <div class="row">
            <div class="col-12">
                <form action="#" method="POST" id="update_sendgrid_api_form">
                    <input type="hidden" name="table_id" id="table_id" value="'.$table_id.'">
                    <div class="row">
                        <div class="col-12">
                            <div class="form-group">
                                <label>'.$this->lang->line('Email Address').'</label>
                                <input type="text" class="form-control" id="updated_sendgrid_email" name="sendgrid_email" value="'.$email.'">
                            </div>
                        </div>

                        <div class="col-12">
                            <div class="form-group">
                                <label>'.$this->lang->line('Username').'</label>
                                <input type="text" class="form-control" id="updated_sendgrid_username" name="sendgrid_username" value="'.$username.'">
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="form-group">
                                <label>'.$this->lang->line('Password').'</label>
                                <input type="text" class="form-control" id="updated_sendgrid_password" name="sendgrid_password" value="'.$password.'">
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="form-group">
                                <label>'.$this->lang->line('Status').'</label><br>
                                <label class="custom-switch">
                                    <input type="checkbox" name="sendgrid_status" value="1" id="updated_sendgrid_status" class="custom-switch-input" '.$status_checked.'>
                                    <span class="custom-switch-indicator"></span>
                                    <span class="custom-switch-description">'.$this->lang->line('Active').'</span>
                                </label>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="col-12"><br>
                <button type="button" class="btn btn-primary btn-lg" id="update_sendgrid"><i class="fas fa-edit"></i> '.$this->lang->line('Update').'</button>
                <button type="button" class="btn btn-light btn-lg float-right" data-dismiss="modal"><i class="fas fa-times"></i> '.$this->lang->line('Close').'</button>
            </div>
        </div>';

        echo $updated_forms;
    }

    public function ajax_sendgrid_api_update()
    {
        if($this->session->userdata('user_type') != 'Admin' && !in_array(263,$this->module_access)) exit;

        $this->ajax_check();

        $save_data = array();
        $ret = array();

        $table_id = $this->input->post("table_id");

        if($_POST)
        {
            $post = $_POST;
            foreach ($post as $key => $value) 
            {
                $$key = $this->input->post($key,TRUE);
            }

            $sendgrid_status = $this->input->post("sendgrid_status",true);
            if($sendgrid_status == "") $sendgrid_status = "0";

            $save_data['user_id']       = $this->user_id;
            $save_data['email_address'] = trim(strip_tags($sendgrid_email));
            $save_data['username']      = trim(strip_tags($sendgrid_username));
            $save_data['password']      = trim(strip_tags($sendgrid_password));
            $save_data['status']        = trim(strip_tags($sendgrid_status));

            if($this->basic->update_data("email_sendgrid_config",array("id"=>$table_id,"user_id"=>$this->user_id),$save_data))
            {
                $ret['status'] = '1';
                $ret['msg'] = $this->lang->line("Sendgrid API Information has been updated successfully.");
            } else
            {
                $ret['status'] = '0';
                $ret['msg'] = $this->lang->line("Something went wrong, please try once again.");
            }

            echo json_encode($ret);
        }
    }

    public function delete_sendgrid_api()
    {
        if($this->session->userdata('user_type') != 'Admin' && !in_array(263,$this->module_access)) exit;

        $this->ajax_check();

        $table_id = $this->input->post("table_id");
        if($table_id == "0" || $table_id == "") exit;

        if($this->basic->delete_data("email_sendgrid_config",array("id"=>$table_id,"user_id"=>$this->user_id)))
        {
            echo "1";
        } else
        {
            echo "0";
        }
    }

    // ===========================================================================================================
    //                                             SMS Campaign Section                                                        
    // ============================================================================================================

    // SMS campaign Creation section started
    public function sms_campaign_lists()
    {
        if($this->session->userdata('user_type') != 'Admin' && !in_array(264,$this->module_access)) redirect('home/login_page', 'location');

        $data['body'] = 'sms_email_manager/sms/sms_campaign_lists';
        $data['page_title'] = $this->lang->line('SMS Campaign');
        $this->_viewcontroller($data);
    }

    public function sms_campaign_lists_data()
    {
        if($this->session->userdata('user_type') != 'Admin' && !in_array(264,$this->module_access)) exit;

        $this->ajax_check();

        $campaign_status     = trim($this->input->post("campaign_status",true));
        $searching_campaign  = trim($this->input->post("searching_campaign",true));
        $post_date_range = $this->input->post("post_date_range",true);

        $display_columns = array("#",'id','campaign_name','send_as','sent_count','actions','posting_status','schedule_time','created_at');

        $page = isset($_POST['page']) ? intval($_POST['page']) : 1;
        $start = isset($_POST['start']) ? intval($_POST['start']) : 0;
        $limit = isset($_POST['length']) ? intval($_POST['length']) : 10;
        $sort_index = isset($_POST['order'][0]['column']) ? strval($_POST['order'][0]['column']) : 1;
        $sort = isset($display_columns[$sort_index]) ? $display_columns[$sort_index] : 'id';
        $order = isset($_POST['order'][0]['dir']) ? strval($_POST['order'][0]['dir']) : 'desc';
        $order_by=$sort." ".$order;

        $where_simple=array();
        $where_simple['sms_sending_campaign.user_id'] = $this->user_id;

        if($post_date_range!="")
        {
            $exp = explode('|', $post_date_range);
            $from_date = isset($exp[0])?$exp[0]:"";
            $to_date   = isset($exp[1])?$exp[1]:"";

            if($from_date!="Invalid date" && $to_date!="Invalid date")
            {
                $from_date = date('Y-m-d', strtotime($from_date));
                $to_date   = date('Y-m-d', strtotime($to_date));
                $where_simple["Date_Format(created_at,'%Y-%m-%d') >="] = $from_date;
                $where_simple["Date_Format(created_at,'%Y-%m-%d') <="] = $to_date;
            }
        }

        if($campaign_status !="") $where_simple['sms_sending_campaign.posting_status'] = $campaign_status;
        if($searching_campaign !="") $where_simple['sms_sending_campaign.campaign_name like'] = "%".$searching_campaign."%";

        $where  = array('where'=>$where_simple);
        $join   = array("sms_api_config" => "sms_sending_campaign.api_id=sms_api_config.id,left");
        $select = array("sms_sending_campaign.*","sms_api_config.gateway_name","sms_api_config.phone_number AS api_phone");

        $table = "sms_sending_campaign";
        $info = $this->basic->get_data($table,$where,$select,$join,$limit,$start,$order_by,$group_by='');

        $total_rows_array = $this->basic->count_row($table,$where,$count=$table.".id",$join,$group_by='');
        $total_result = $total_rows_array[0]['total_rows'];

        for ($i=0; $i < count($info) ; $i++) 
        { 
            $action_count = 3;
            $posting_status = $info[$i]['posting_status'];

            if($info[$i]['api_phone'] != "")
                $info[$i]['send_as'] = $info[$i]['gateway_name'].' : '.$info[$i]['api_phone'];
            else
                $info[$i]['send_as'] = $info[$i]['gateway_name'];

            if($info[$i]['schedule_time'] != "0000-00-00 00:00:00")
                $info[$i]['schedule_time'] = "<div style='min-width:100px !important;'>".date("M j, y H:i",strtotime($info[$i]['schedule_time']))."</div>";
            else 
                $info[$i]['schedule_time'] = "<div style='min-width:100px !important;' class='text-muted'><i class='fas fa-exclamation-circle'></i> ".$this->lang->line('Not Scheduled')."</div>";

            // added date
            if($info[$i]['created_at'] != "0000-00-00 00:00:00")
                $info[$i]['created_at'] = "<div style='min-width:100px !important;'>".date("M j, y H:i",strtotime($info[$i]['created_at']))."</div>";

            // generating delete button
            if($posting_status=='1')
                $delete_btn = "<a href='#' class='btn btn-circle btn-light pointer text-muted' data-toggle='tooltip' title='".$this->lang->line("Campaign in processing can not be deleted. You can pause campaign and then delete it.")."'><i class='fa fa-trash'></i></a>";
            else 
                $delete_btn =  "<a href='#' data-toggle='tooltip' title='".$this->lang->line("delete campaign")."' id='".$info[$i]['id']."' class='delete_sms_campaign btn btn-circle btn-outline-danger'><i class='fa fa-trash'></i></a>";

            $is_try_again = $info[$i]["is_try_again"];

            $force_porcess_str="";

            $number_of_sms_to_be_sent_in_try = $this->config->item("number_of_sms_to_be_sent_in_try");
            if($number_of_sms_to_be_sent_in_try == "") $number_of_sms_to_be_sent_in_try = 10;

            // generating restat and force processing button
            if($number_of_sms_to_be_sent_in_try == "" ||  $number_of_sms_to_be_sent_in_try == "0")
            {
                $force_porcess_str="";
            }
            else
            {
                $action_count++;
                if($posting_status=='1' && $is_try_again=='1')
                    $force_porcess_str .= "<a href='#' class='btn btn-circle btn-outline-dark pause_campaign_info' table_id='".$info[$i]['id']."' data-toggle='tooltip' title='".$this->lang->line("Pause Campaign")."'><i class='fas fa-pause'></i></a>";
                if($posting_status=='3')
                    $force_porcess_str .= "<a href='#' class='btn btn-circle btn-outline-success play_campaign_info' table_id='".$info[$i]['id']."' data-toggle='tooltip' title='".$this->lang->line("Start Campaign")."'><i class='fas fa-play'></i></a>";
            }

            if($posting_status=='1'){
                $action_count++;
                $force_porcess_str .= "<a href='#' id='".$info[$i]['id']."' class='force btn btn-circle btn-outline-warning' data-toggle='tooltip' title='".$this->lang->line("force reprocessing")."'><i class='fas fa-sync'></i></a>";
            }

            // status
            if( $posting_status == '2') 
                $info[$i]['posting_status'] = '<div style="min-width:100px"><span class="text-success badge"><i class="fas fa-check-circle"></i> '.$this->lang->line("Completed").'</span></div>';
            else if( $posting_status == '1') 
                $info[$i]['posting_status'] = '<div style="min-width:100px"><span class="text-warning"><i class="fas fa-spinner"></i> '.$this->lang->line("Processing").'</span></div>';
            else if( $posting_status == '3') 
                $info[$i]['posting_status'] = '<div style="min-width:100px"><span class="text-muted"><i class="fas fa-stop"></i> '.$this->lang->line("Paused").'</span></div>';
            else 
                $info[$i]['posting_status'] = '<div style="min-width:100px"><span class="text-danger"><i class="far fa-times-circle"></i> '.$this->lang->line("Pending").'</span></div>';

            // sent column
            $info[$i]["sent_count"] =  $info[$i]["successfully_sent"]."/". $info[$i]["total_thread"] ;

            $report_btn = "<a href='#' class='campaign_report btn btn-circle btn-outline-primary' data-toggle='tooltip' title='".$this->lang->line("View Report")."' 
                table_id='".$info[$i]['id']."' 
                campaign_name='".$info[$i]['campaign_name']."' 
                campaign_message='".$info[$i]['campaign_message']."'
                send_as='".$info[$i]['send_as']."' 
                campaign_status='".$posting_status."' 
                successfullysent='".$info[$i]["successfully_sent"]."' 
                totalThread='".$info[$i]["total_thread"]."' 
                ><i class='fas fa-eye'></i> </a>";

            if($posting_status != '0' || $info[$i]['time_zone'] == "") 
                $edit_btn = "<a href='#' data-toggle='tooltip' title='".$this->lang->line("only pending campaigns are editable")."' class='btn btn-circle btn-light'><i class='fas fa-edit'></i></a>";
            else
            {
                $edit_url = site_url('sms_email_manager/edit_sms_campaign/'.$info[$i]['id']);
                $edit_btn =  "<a data-toggle='tooltip' title='".$this->lang->line('edit campaign')."' href='".$edit_url."' class='btn btn-circle btn-outline-warning'><i class='fas fa-edit'></i></a>";
            }


            $action_width = ($action_count*47)+20;
            $info[$i]['actions'] ='
            <div class="dropdown d-inline dropright">
              <button class="btn btn-outline-primary dropdown-toggle no_caret" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                <i class="fa fa-briefcase"></i>
              </button>
              <div class="dropdown-menu mini_dropdown text-center" style="width:'.$action_width.'px !important">';
                $info[$i]['actions'] .= $report_btn;
                $info[$i]['actions'] .= $edit_btn;
                $info[$i]['actions'] .= $force_porcess_str;
                $info[$i]['actions'] .= $delete_btn;
                $info[$i]['actions'] .="
              </div>
            </div>
            <script>
            $('[data-toggle=\"tooltip\"]').tooltip();</script>";
        }

        $data['draw'] = (int)$_POST['draw'] + 1;
        $data['recordsTotal'] = $total_result;
        $data['recordsFiltered'] = $total_result;
        $data['data'] = convertDataTableResult($info, $display_columns ,$start,$primary_key="id");

        echo json_encode($data);
    }

    public function delete_sms_campaign()
    {
        if($this->session->userdata('user_type') != 'Admin' && !in_array(264,$this->module_access)) exit;

        $this->ajax_check();

        $id = $this->input->post("campaign_id");
        if($id == "" || $id=="0") exit;

        $campaign_data = $this->basic->get_data("sms_sending_campaign",array("where"=>array("id"=>$id,"user_id"=>$this->user_id)),array("posting_status","total_thread"));

        $current_total_thread_abs  = isset($campaign_data[0]["total_thread"]) ? $campaign_data[0]["total_thread"] : 0;
        $posting_status  = isset($campaign_data[0]["posting_status"]) ? $campaign_data[0]["posting_status"] : "";

        if($posting_status=="0") // removing usage data if deleted and campaign is pending
        {
            if($current_total_thread_abs>0)
                $this->_delete_usage_log($module_id=264,$request=$current_total_thread_abs);
        }

        if($this->basic->delete_data("sms_sending_campaign",array("id"=>$id,"user_id"=>$this->user_id)))
        {
            if($this->basic->delete_data("sms_sending_campaign_send",array("campaign_id"=>$id,"user_id"=>$this->user_id))){
                echo "1";
            }
        }
        else {
            echo "0";
        }
    }


    public function ajax_get_sms_campaign_report_info()
    {
        if($this->session->userdata('user_type') != 'Admin' && !in_array(264,$this->module_access)) exit;

        $this->ajax_check();

        $table_id = $this->input->post('table_id');
        $searching = trim($this->input->post("searching",true));

        $display_columns = array("#","contact_first_name","contact_last_name","contact_phone_number","sent_time","delivery_id");

        $page = isset($_POST['page']) ? intval($_POST['page']) : 1;
        $start = isset($_POST['start']) ? intval($_POST['start']) : 0;
        $limit = isset($_POST['length']) ? intval($_POST['length']) : 10;
        $sort_index = isset($_POST['order'][0]['column']) ? strval($_POST['order'][0]['column']) : 1;
        $sort = isset($display_columns[$sort_index]) ? $display_columns[$sort_index] : 'id';
        $order = isset($_POST['order'][0]['dir']) ? strval($_POST['order'][0]['dir']) : 'desc';
        $order_by=$sort." ".$order;

        $where['where'] = array('id'=> $table_id);

        $table="sms_sending_campaign";
        $info = $this->basic->get_data($table,$where,$select='');

        if(isset($info[0]['report']) && $info[0]['report'] != '')
        {
            $campaign_details = $info[0];

            $report_info = json_decode($campaign_details['report'],true);
            $reply_info = $report_info;

            $reply_info = array_filter($reply_info, function($single_reply) use ($searching) 
            {
                if ($searching != '') {

                    if (stripos($single_reply['contact_username'], $searching) !== false || stripos($single_reply['contact_phone_number'], $searching) !== false) 
                    {
                        return TRUE; 
                    }
                    else
                    {
                        return FALSE;  
                    }
                }
                else
                {
                    return TRUE;
                }

            });


            usort($reply_info, function($first, $second) use ($sort, $order)
            {
                if ($first[$sort] == $second[$sort]) {
                    return 0;
                }
                else if ($first[$sort] > $second[$sort]) {
                    if ($order == 'desc') return 1;
                    else return -1;
                }
                else if ($first[$sort] < $second[$sort]) {
                    if ($order == 'desc') return -1;
                    else return 1;
                }

            });


            $final_info = array();
            $i = 0;
            $upper_limit = $start + $limit;

            foreach ($reply_info as $key => $value) { 

                if ($i >= $start && $i < ($upper_limit))
                    array_push($final_info, $value);

                $i++;
            }

            $result = array();
            foreach ($final_info as $value) {

                $temp = array();
                array_push($temp, ++$start);

                $contact_first_name = $value['contact_first_name'];
                $contact_last_name = $value['contact_last_name'];
                $sentime = $value['sent_time'];

                if($value['sent_time'] == "pending") $sentTime = "x";

                foreach ($value as $key => $column) 
                {
                    if($key=='contact_first_name' && $contact_first_name == "")
                        $column = "<div class='text-center'>-</div>";

                    if($key=='contact_last_name' && $contact_last_name == "")
                        $column = "<div class='text-center'>-</div>";

                    if ($key == 'sent_time' && $sentime != "pending")
                        $column = "<div style='min-width:110px;'>".date('M j, y H:i', strtotime($column))."</div>";                    

                    if ($key == 'sent_time' && $column == 'pending')
                        $column = 'x';

                    if($key=='delivery_id' && $column == 'pending')
                        $column = '<div style="min-width:100px"><span class="text-danger"><i class="fas fa-times"></i> '.ucfirst($column).'</span></div>';

                    if (in_array($key, $display_columns)) 
                        array_push($temp, $column);
                }

                array_push($result, $temp);
            }

        }
        else {

            $total_result = 0;
            $reply_info = array();
            $result = array();
        }

        $total_result = count($reply_info);
        $data['draw'] = (int)$_POST['draw'] + 1;
        $data['recordsTotal'] = $total_result;
        $data['recordsFiltered'] = $total_result;
        $data['data'] = $result;


        echo json_encode($data);
    }

    public function edit_campaign_content($id=0)
    {
        if($this->session->userdata('user_type') != 'Admin' && !in_array(264,$this->module_access)) redirect('home/login_page', 'location');

        if($id==0) exit();

        $data['body'] = "sms_email_manager/sms/edit_message_content";
        $data['page_title'] = $this->lang->line("Edit Message Contents");
        $data["message_data"] = $this->basic->get_data("sms_sending_campaign",array("where"=>array("id"=>$id,"user_id"=>$this->user_id)));
        $this->_viewcontroller($data);
    }

    public function edit_campaign_content_action()
    {
        if($this->session->userdata('user_type') != 'Admin' && !in_array(264,$this->module_access)) exit;

        $this->ajax_check();

        $table_id = $this->input->post("table_id",true);
        $user_id = $this->user_id;
        $message = $this->input->post("message");
        $message = str_replace(array("'",'"'), array('`','`'), $message);
        $edited_message   = array('campaign_message' => $message);

        if($this->basic->update_data('sms_sending_campaign',array("id"=>$table_id,"user_id"=>$this->user_id),$edited_message))
        {
            echo "1";
        } else
        {
            echo "0";
        }
        
    }

    public function restart_campaign()
    {
        if($this->session->userdata('user_type') != 'Admin' && !in_array(264,$this->module_access)) exit;

        $this->ajax_check();
        $id = $this->input->post("table_id");

        $where = array('id'=>$id,'user_id'=>$this->user_id);
        $data = array('is_try_again'=>'1','posting_status'=>'1');
        $this->basic->update_data('sms_sending_campaign',$where,$data);
        echo '1';
    }

    public function ajax_sms_campaign_pause()
    {
        if($this->session->userdata('user_type') != 'Admin' && !in_array(264,$this->module_access)) exit;

        $this->ajax_check();
        $table_id = $this->input->post('table_id');
        $post_info = $this->basic->update_data('sms_sending_campaign',array('id'=>$table_id),array('posting_status'=>'3','is_try_again'=>'0'));
        echo '1';
        
    }

    public function ajax_sms_campaign_play()
    {
        if($this->session->userdata('user_type') != 'Admin' && !in_array(264,$this->module_access)) exit;

        $this->ajax_check();
        $table_id = $this->input->post('table_id');
        $post_info = $this->basic->update_data('sms_sending_campaign',array('id'=>$table_id),array('posting_status'=>'1','is_try_again'=>'1'));
        echo '1';
    }

    public function force_reprocess_sms_campaign()
    {
        if($this->session->userdata('user_type') != 'Admin' && !in_array(264,$this->module_access)) exit;

        $this->ajax_check();
        $id = $this->input->post("id");

        $where = array('id'=>$id,'user_id'=>$this->user_id);
        $data = array('is_try_again'=>'1','posting_status'=>'1');
        $this->basic->update_data('sms_sending_campaign',$where,$data);
        if($this->db->affected_rows() != 0) echo "1";
        else  echo "0";
    }

    public function get_subscribers_phone()
    {
        if($this->session->userdata('user_type') != 'Admin' && !in_array(264,$this->module_access)) exit;

        if($this->session->userdata('logged_in') != 1) exit();
        $this->ajax_check();
        $page_id=$this->input->post('page_id');// database id
        $user_gender=$this->input->post('user_gender');
        $user_time_zone=$this->input->post('user_time_zone');
        $user_locale=$this->input->post('user_locale');
        $load_label=$this->input->post('load_label');
        $label_ids=$this->input->post('label_ids');
        $excluded_label_ids=$this->input->post('excluded_label_ids');

        if(!isset($label_ids) || !is_array($label_ids)) $label_ids =array();
        if(!isset($excluded_label_ids) || !is_array($excluded_label_ids)) $excluded_label_ids =array();

        $table_type = 'messenger_bot_broadcast_contact_group';
        $where_type['where'] = array('user_id'=>$this->user_id,"page_id"=>$page_id,"unsubscribe"=>"0","invisible"=>"0");
        $info_type = $this->basic->get_data($table_type,$where_type,$select='', $join='', $limit='', $start='', $order_by='group_name');

        $result = array();
        date_default_timezone_set('UTC');
        $current_time  = date("Y-m-d H:i:s");
        $previous_time = date("Y-m-d H:i:s",strtotime('-23 hour',strtotime($current_time)));
        $this->_time_zone_set();
        $dropdown=array();
        $str = $str2 = "";

        if($load_label=='1')
        {
            $str='<script>$("#label_ids").select2();</script> ';
            $str2='<script>$("#excluded_label_ids").select2();</script> ';
            $str .='<select multiple="multiple"  class="form-control" id="label_ids" name="label_ids[]" style="width:100%;">';
            $str2.='<select multiple="multiple"  class="form-control" id="excluded_label_ids" name="excluded_label_ids[]" style="width:100%;">';        

            foreach ($info_type as  $value)
            {                
                $str.=  "<option value='".$value['id']."'>".$value['group_name']."</option>";
                $str2.= "<option value='".$value['id']."'>".$value['group_name']."</option>"; 
            }

            $str.= '</select>';
            $str2.='</select>';
        }

        $pageinfo = $this->basic->get_data("facebook_rx_fb_page_info",array("where"=>array("id"=>$page_id,"user_id"=>$this->user_id)));
        $page_info = isset($pageinfo[0])?$pageinfo[0]:array();

        if(isset($page_info['page_access_token'])) unset($page_info['page_access_token']);

        $subscriber_count = 0;

        $where_simple2 =array("page_table_id"=>$page_id,'is_bot_subscriber'=> '1','phone_number != '=>'','unavailable'=>'0','user_id'=>$this->user_id,'permission'=>'1');

        if(isset($user_gender) && $user_gender!="")  $where_simple2['messenger_bot_subscriber.gender'] = $user_gender;
        if(isset($user_time_zone) && $user_time_zone!="")  $where_simple2['messenger_bot_subscriber.timezone'] = $user_time_zone;
        if(isset($user_locale) && $user_locale!="")  $where_simple2['messenger_bot_subscriber.locale'] = $user_locale;
    
        $sql_part = "";
        if($load_label=='0')
        {
           if(count($label_ids)>0) $sql_part="("; else $sql_part="";        
           $sql_part_array=array();
           foreach ($label_ids as $key => $value) 
           {
              $sql_part_array[]="FIND_IN_SET('".$value."',contact_group_id) !=0";
           }
           $sql_part.=implode(' OR ', $sql_part_array);
           if(count($label_ids)>0) $sql_part.=")";
           if($sql_part!="") $this->db->where($sql_part);

           foreach ($excluded_label_ids as $key => $value) 
           {
              $sq="NOT FIND_IN_SET('".$value."',contact_group_id) !=0";
              $this->db->where($sq);
           }
        }

        $where2 = array('where'=>$where_simple2);
        $bot_subscriber=$this->basic->get_data("messenger_bot_subscriber",$where2,'count(id) as subscriber_count');
        $subscriber_count = isset($bot_subscriber[0]['subscriber_count'])? $bot_subscriber[0]['subscriber_count'] : 0;
        $page_info['subscriber_count'] = $subscriber_count;

        $page_total_subscribers = $this->basic->get_data("messenger_bot_subscriber",array("where"=>array("page_table_id"=>$page_id,'is_bot_subscriber'=> '1','phone_number != '=>'','unavailable'=>'0','user_id'=>$this->user_id,'permission'=>'1')));
        $page_info['page_total_subscribers'] = count($page_total_subscribers);

        echo json_encode(array('first_dropdown'=>$str,'second_dropdown'=>$str2,"pageinfo"=>$page_info));
    }

    public function get_subscribers_email()
    {
        if($this->session->userdata('user_type') != 'Admin' && !in_array(264,$this->module_access)) exit;

        if($this->session->userdata('logged_in') != 1) exit();
        $this->ajax_check();
        $page_id=$this->input->post('page_id');// database id
        $user_gender=$this->input->post('user_gender');
        $user_time_zone=$this->input->post('user_time_zone');
        $user_locale=$this->input->post('user_locale');
        $load_label=$this->input->post('load_label');
        $label_ids=$this->input->post('label_ids');
        $excluded_label_ids=$this->input->post('excluded_label_ids');

        if(!isset($label_ids) || !is_array($label_ids)) $label_ids =array();
        if(!isset($excluded_label_ids) || !is_array($excluded_label_ids)) $excluded_label_ids =array();

        $table_type = 'messenger_bot_broadcast_contact_group';
        $where_type['where'] = array('user_id'=>$this->user_id,"page_id"=>$page_id,"unsubscribe"=>"0","invisible"=>"0");
        $info_type = $this->basic->get_data($table_type,$where_type,$select='', $join='', $limit='', $start='', $order_by='group_name');

        $result = array();
        date_default_timezone_set('UTC');
        $current_time  = date("Y-m-d H:i:s");
        $previous_time = date("Y-m-d H:i:s",strtotime('-23 hour',strtotime($current_time)));
        $this->_time_zone_set();
        $dropdown=array();
        $str = $str2 = "";

        if($load_label=='1')
        {
            $str='<script>$("#label_ids").select2();</script> ';
            $str2='<script>$("#excluded_label_ids").select2();</script> ';
            $str .='<select multiple="multiple"  class="form-control" id="label_ids" name="label_ids[]" style="width:100%;">';
            $str2.='<select multiple="multiple"  class="form-control" id="excluded_label_ids" name="excluded_label_ids[]" style="width:100%;">';        

            foreach ($info_type as  $value)
            {                
                $str.=  "<option value='".$value['id']."'>".$value['group_name']."</option>";
                $str2.= "<option value='".$value['id']."'>".$value['group_name']."</option>"; 
            }

            $str.= '</select>';
            $str2.='</select>';
        }

        $pageinfo = $this->basic->get_data("facebook_rx_fb_page_info",array("where"=>array("id"=>$page_id,"user_id"=>$this->user_id)));
        $page_info = isset($pageinfo[0])?$pageinfo[0]:array();

        if(isset($page_info['page_access_token'])) unset($page_info['page_access_token']);

        $subscriber_count = 0;

        $where_simple2 =array("page_table_id"=>$page_id,'is_bot_subscriber'=> '1','email != '=>'','unavailable'=>'0','user_id'=>$this->user_id,'permission'=>'1');

        if(isset($user_gender) && $user_gender!="")  $where_simple2['messenger_bot_subscriber.gender'] = $user_gender;
        if(isset($user_time_zone) && $user_time_zone!="")  $where_simple2['messenger_bot_subscriber.timezone'] = $user_time_zone;
        if(isset($user_locale) && $user_locale!="")  $where_simple2['messenger_bot_subscriber.locale'] = $user_locale;
    
        $sql_part = "";
        if($load_label=='0')
        {
           if(count($label_ids)>0) $sql_part="("; else $sql_part="";        
           $sql_part_array=array();
           foreach ($label_ids as $key => $value) 
           {
              $sql_part_array[]="FIND_IN_SET('".$value."',contact_group_id) !=0";
           }
           $sql_part.=implode(' OR ', $sql_part_array);
           if(count($label_ids)>0) $sql_part.=")";
           if($sql_part!="") $this->db->where($sql_part);

           foreach ($excluded_label_ids as $key => $value) 
           {
              $sq="NOT FIND_IN_SET('".$value."',contact_group_id) !=0";
              $this->db->where($sq);
           }
        }

        $where2 = array('where'=>$where_simple2);
        $bot_subscriber=$this->basic->get_data("messenger_bot_subscriber",$where2,'count(id) as subscriber_count');
        $subscriber_count = isset($bot_subscriber[0]['subscriber_count'])? $bot_subscriber[0]['subscriber_count'] : 0;
        $page_info['subscriber_count'] = $subscriber_count;

        $page_total_subscribers = $this->basic->get_data("messenger_bot_subscriber",array("where"=>array("page_table_id"=>$page_id,'is_bot_subscriber'=> '1','email != '=>'','unavailable'=>'0','user_id'=>$this->user_id,'permission'=>'1')));
        $page_info['page_total_subscribers'] = count($page_total_subscribers);

        echo json_encode(array('first_dropdown'=>$str,'second_dropdown'=>$str2,"pageinfo"=>$page_info));
    }

    public function contacts_total_numbers()
    {
        if($this->session->userdata('user_type') != 'Admin' && !in_array(264,$this->module_access)) exit;

        if(!$_POST) exit;
        $this->ajax_check();

        $user_id = $this->user_id;
        $contacts_sms_group = $this->input->post('contact_ids', true);

        if(isset($contacts_sms_group) && !empty($contacts_sms_group))
        foreach ($contacts_sms_group as $key => $value) 
        {
            $where_simple = array('sms_email_contacts.user_id'=>$this->user_id,"sms_email_contacts.phone_number !="=>"");
            $this->db->where("FIND_IN_SET('$value',sms_email_contacts.contact_type_id) !=", 0);
            $where = array('where'=>$where_simple);    
            $contact_details = $this->basic->get_data('sms_email_contacts', $where,array("phone_number"));

            foreach ($contact_details as $key2 => $value2) 
            {   
                $contacts_id[] = isset($value2["id"]) ? $value2["id"]: "";
            }
        }

        $total_contact = count($contacts_id);
        echo $total_contact;

    }

    public function create_sms_campaign()
    {
        if($this->session->userdata('user_type') != 'Admin' && !in_array(264,$this->module_access)) redirect('home/login_page', 'location');

        /**Get contact number and sms_email_contact_group***/
        $user_id = $this->user_id;
        $table_type = 'sms_email_contact_group';   
        $where_type['where'] = array('user_id'=>$user_id);
        $info_type = $this->basic->get_data($table_type,$where_type,$select='', $join='', $limit='', $start='', $order_by='type');

        $page_info = $this->basic->get_data("facebook_rx_fb_page_info",array("where"=>array("user_id"=>$this->user_id,"facebook_rx_fb_user_info_id"=>$this->session->userdata("facebook_rx_fb_user_info"),"bot_enabled"=>'1')),$select='',$join='',$limit='',$start=NULL,$order_by='page_name ASC');
        $data['page_info'] = $page_info;  
        $result = array();

        if(isset($info_type) && !empty($info_type))
        {
            foreach ($info_type as  $value) 
            {
                $search_key  = $value['id'];
                $search_type = $value['type'];

                $where_simple = array('sms_email_contacts.user_id'=>$user_id);
                $this->db->where("FIND_IN_SET('$search_key',sms_email_contacts.contact_type_id) !=", 0);
                $this->db->where('unsubscribed !=', 0);
                $where = array('where'=>$where_simple);

                $this->db->select("count(sms_email_contacts.id) as number_count",false);    

                $contact_details = $this->basic->get_data('sms_email_contacts', $where, $select='', $join='', $limit='', $start='', $order_by='sms_email_contacts.first_name', $group_by='', $num_rows=0);
                foreach ($contact_details as $key2 => $value2) 
                {
                    if($value2['number_count']>0)
                    $group_name[$search_key] = $search_type." (".$value2['number_count'].")";
                }
                    
            }  
        }   


        $where_simple = array();
        $temp_userid = $user_id;

        /***get sms config***/
        $apiAccess = $this->config->item('sms_api_access');
        if($this->config->item('sms_api_access') == "") $apiAccess = "0";

        if(isset($apiAccess) && $apiAccess == '1' && $this->session->userdata("user_type") == 'Member')
        {
            $join = array('users' => 'sms_api_config.user_id=users.id,left');
            $select = array('sms_api_config.*','users.id AS usersId','users.user_type');
            $where_in = array('sms_api_config.user_id'=>array('1',$temp_userid),'users.user_type'=>array('Admin','Member'));
            $where = array('where'=> array('sms_api_config.status'=>'1'),'where_in'=>$where_in);
            $sms_api_config=$this->basic->get_data('sms_api_config', $where, $select, $join, $limit='', $start='', $order_by='phone_number ASC', $group_by='', $num_rows=0);
        } else
        {
            $where = array("where" => array('user_id'=>$temp_userid,'status'=>'1'));
            $sms_api_config=$this->basic->get_data('sms_api_config', $where, $select='', $join='', $limit='', $start='', $order_by='phone_number ASC', $group_by='', $num_rows=0);
        }

        $sms_api_config_option=array();
        foreach ($sms_api_config as $info) {
            $id=$info['id'];

            if($info['phone_number'] !="")
                $sms_api_config_option[$id]=$info['gateway_name'].": ".$info['phone_number'];
            else
                $sms_api_config_option[$id]=$info['gateway_name'];
        }

        $data['sms_option'] = $sms_api_config_option;
        $data['groups_name']  = isset($group_name) ? $group_name: "";
        $data['time_zone']  = $this->_time_zone_list();
        $data["time_zone_numeric"]= $this->_time_zone_list_numeric();
        $data['locale_list'] = $this->sdk_locale();
        $data['body'] = 'sms_email_manager/sms/create_sms_campaigns';
        $data['page_title'] = $this->lang->line('Create SMS Campaign');
        $this->_viewcontroller($data);
        
    }

    public function create_sms_campaign_action()
    {
        if($this->session->userdata('user_type') != 'Admin' && !in_array(264,$this->module_access)) exit;

        if(!$_POST) exit();
        $this->ajax_check();

        if($this->is_demo == '1')
        {
            if($this->session->userdata('user_type') == "Admin")
            {                
                echo json_encode(array('status'=>'0','message'=>$this->lang->line("This action is disabled in this demo account. Please signup as user and try this with your account")));
                exit();
            }
        }

        $report = array();

        $campaign_name = strip_tags(trim($this->input->post('campaign_name', true)));
        $message       = $this->input->post('message', true);
        $schedule_time = $this->input->post('schedule_time');
        $time_zone     = strip_tags(trim($this->input->post('time_zone', true)));
        $sms_api       = strip_tags(trim($this->input->post('from_sms', true)));
        $to_numbers    = trim($this->input->post('to_numbers', true));
        $country_code_add  = trim($this->input->post('country_code_add', true));
        $country_code_remove  = trim($this->input->post('country_code_remove', true));

        $page_auto_id = $this->input->post('page',true); // page auto id
        $label_ids = $this->input->post('label_ids',true);
        $excluded_label_ids = $this->input->post('excluded_label_ids',true);
        $user_gender = $this->input->post('user_gender',true);
        $user_time_zone = $this->input->post('user_time_zone',true);
        $user_locale = $this->input->post('user_locale',true);

        if(!isset($label_ids) || !is_array($label_ids)) $label_ids=array();
        if(!isset($excluded_label_ids) || !is_array($excluded_label_ids)) $excluded_label_ids=array();

        if($time_zone=='') $time_zone = "Asia/Novosibirsk";

        $successfully_sent = 0;
        $added_at          = date("Y-m-d H:i:s");
        $posting_status    = "0";

        // Messenger Subscriber Section Started
        if(isset($page_auto_id) && !empty($page_table_id))
        {
            $pageinfo = $this->basic->get_data("facebook_rx_fb_page_info",array("where"=>array("id"=>$page_auto_id,"user_id"=>$this->user_id)));
            if(!isset($pageinfo[0]))
            {
                echo json_encode(array('status'=>'0','message'=>$this->lang->line("Something went wrong.")));
                exit();
            }
            $fb_page_id  = $pageinfo[0]['page_id'];
            $page_name  = $pageinfo[0]['page_name'];

            $excluded_label_ids_temp=$excluded_label_ids;
            $unsubscribe_labeldata=$this->basic->get_data("messenger_bot_broadcast_contact_group",array("where"=>array("user_id"=>$this->user_id,"page_id"=>$page_auto_id,"unsubscribe"=>"1")));
            foreach ($unsubscribe_labeldata as $key => $value) 
            {
                array_push($excluded_label_ids_temp, $value["id"]);
            }

            if(count($label_ids)>0) $sql_part="("; else $sql_part="";        
            $sql_part_array=array();
            foreach ($label_ids as $key => $value) 
            {
               $sql_part_array[]="FIND_IN_SET('".$value."',contact_group_id) !=0";
            }        
            if(count($label_ids)>0) 
            {
                $sql_part.=implode(' OR ', $sql_part_array);
                $sql_part.=") AND ";
            }

            $sql_part2="";
            $sql_part_array2=array();
            foreach ($excluded_label_ids_temp as $key => $value) 
            {
              $sql_part_array2[]="NOT FIND_IN_SET('".$value."',contact_group_id) !=0";          
            }        
            if(count($excluded_label_ids_temp)>0) 
            {
                $sql_part2=implode(' AND ', $sql_part_array2);
                $sql_part2.=" AND ";
            }

            $sql_part3="";
            $sql_part_array3 = array();
            if($user_gender!='') $sql_part_array3[] = "gender = '{$user_gender}'";
            if($user_time_zone!='') $sql_part_array3[] = "timezone = '{$user_time_zone}'";
            if($user_locale!='') $sql_part_array3[] = "locale = '{$user_locale}'";

            if(count($sql_part_array3)>0) 
            {
                $sql_part3 = implode(' AND ', $sql_part_array3);
                $sql_part3 .=" AND ";
            }

            $sql="SELECT * FROM messenger_bot_subscriber WHERE ".$sql_part." ".$sql_part2." ".$sql_part3." user_id = ".$this->user_id." AND page_table_id = {$page_auto_id} AND is_bot_subscriber='1' AND phone_number!='' AND permission='1' AND unavailable_conversation='0';";
            $lead_list = $this->basic->execute_query($sql);

            if(isset($lead_list) && !empty($lead_list)){
                foreach ($lead_list as $lead_key => $lead_value) {

                    if($lead_value['phone_number'] == "") continue;

                    if(isset($country_code_add) & $country_code_add != '')
                    {
                        if(!preg_match("/^\+?{$country_code_add}/",$lead_value['phone_number'])) 
                        {
                            $lead_value['phone_number'] = $country_code_add.$lead_value['phone_number'];
                        }
                    }
                    else if(isset($country_code_remove) && $country_code_remove != '')
                    {
                        // $lead_value['phone_number'] = preg_replace("/^\+?{$country_code_remove}/", '',$lead_value['phone_number']);
                        if(preg_match("/^\+?{$country_code_remove}/",$lead_value['phone_number'])) {
                            $lead_value['phone_number'] = preg_replace("/^\+?{$country_code_remove}/",'',$lead_value['phone_number']);
                        }
                    }

                    $report[$lead_value['phone_number']] = array(
                        'api_id'              => $sms_api,
                        'contact_id'          => '0',
                        'subscriber_id'       => $lead_value['id'],
                        'contact_first_name'  => isset($lead_value['first_name']) ? $lead_value['first_name']:"",
                        'contact_last_name'   => isset($lead_value['last_name']) ? $lead_value['last_name']:"",
                        'contact_email'       => isset($lead_value['email']) ? $lead_value['email']:"",
                        'contact_phone_number'=> isset($lead_value['phone_number']) ? $lead_value['phone_number']:"",
                        'sent_time'           =>'pending',
                        'delivery_id'         =>'pending',
                    );
                }
            }
        }
        // Messenger Subscriber Section Ended


        // Contact Group Section Started
        if(isset($to_numbers) && !empty($to_numbers))
        {
            $exploded_to_numbers = explode(',',$to_numbers);
            $exploded_to_numbers = array_unique($exploded_to_numbers);
        }

        $contacts_sms_group = $this->input->post('contacts_id', true);
        if(isset($contacts_sms_group) && !empty($contacts_sms_group))
            $contact_groupid    = implode(",",$contacts_sms_group);

        $manual_numbers = array();
        $contacts_id = array();


        if(isset($contacts_sms_group) && !empty($contacts_sms_group)){
            foreach ($contacts_sms_group as $key => $value) 
            {
                $where_simple = array('sms_email_contacts.user_id'=>$this->user_id);
                $this->db->where("FIND_IN_SET('$value',sms_email_contacts.contact_type_id) !=", 0);
                $where = array('where'=>$where_simple);    
                $contact_details = $this->basic->get_data('sms_email_contacts', $where);

                foreach ($contact_details as $key2 => $value2) 
                {   
                    if($value2['phone_number'] == "") continue;

                    if(isset($country_code_add) & $country_code_add != '')
                    {
                        if(!preg_match("/^\+?{$country_code_add}/",$value2['phone_number'])) 
                        {
                            $value2['phone_number'] = $country_code_add.$value2['phone_number'];
                        }
                    }
                    else if(isset($country_code_remove) && $country_code_remove != '')
                    {
                        // $value2['phone_number'] = preg_replace("/^\+?{$country_code_remove}/", '',$value2['phone_number']);
                        if(preg_match("/^\+?{$country_code_remove}/",$value2['phone_number'])) {
                            $value2['phone_number'] = preg_replace("/^\+?{$country_code_remove}/",'',$value2['phone_number']);
                        }
                    }                    

                    $report[$value2['phone_number']] = array(
                        'api_id'              => $sms_api,
                        'contact_id'          => $value2['id'],
                        'subscriber_id'       => "0",
                        'contact_first_name'  => isset($value2['first_name']) ? $value2['first_name']:"",
                        'contact_last_name'   => isset($value2['last_name']) ? $value2['last_name']:"",
                        'contact_email'       => isset($value2['email']) ? $value2['email']:"",
                        'contact_phone_number'=> isset($value2['phone_number']) ? $value2['phone_number']:"",
                        'sent_time'           =>'pending',
                        'delivery_id'         =>'pending',
                    );

                    $contacts_id[] = isset($value2["id"]) ? $value2["id"]: "";
                }
            }
        }

        $contacts_id = array_filter($contacts_id);
        $contacts_id = array_unique($contacts_id);
        $contacts_id = implode(',', $contacts_id);

        // for manual phone number insertion
        $manual_thread = 0;
        if(isset($exploded_to_numbers))
        {
            foreach ($exploded_to_numbers as $single_values) 
            {
                if(isset($country_code_add) & $country_code_add != '')
                {
                    if(!preg_match("/^\+?{$country_code_add}/",$single_values)) 
                    {
                         $single_values = $country_code_add.$single_values;
                    }
                }
                else if(isset($country_code_remove) && $country_code_remove != '')
                {
                    // $single_values = preg_replace("/^\+?{$country_code_remove}/", '',$single_values);
                    if(preg_match("/^\+?{$country_code_remove}/",$single_values)) {
                        $single_values = preg_replace("/^\+?{$country_code_remove}/",'',$single_values);
                    }
                }

                $report[$single_values] = array(
                    'api_id' => $sms_api,
                    'contact_id' => '0',
                    'subscriber_id' => '0',
                    'contact_first_name'=> "",
                    'contact_last_name'=> "",
                    'contact_email'=> "",
                    'contact_phone_number'=>$single_values,
                    'sent_time' =>'pending',
                    'delivery_id' =>'pending',
                );

                $manual_thread++;
            }
        }
        // Contact Group Section Ended

        $thread = count($report);

        if($thread==0)
        {
            echo json_encode(array('status'=>'0','message'=>$this->lang->line("Campaign could not target any subscriber with phone number to reach message. Please try again with different targeting options.")));
            exit();
        }

        // inserting data of sms_campaign_campaign Table
        $inserted_data = array(
            "user_id"           => $this->user_id,
            "api_id"            => $sms_api,
            'page_id'           => isset($page_auto_id) ? $page_auto_id:"",
            'fb_page_id'        => isset($fb_page_id) ? $fb_page_id:"", 
            'page_name'         => isset($page_name) ? $page_name:"",
            "contact_ids"       => isset($contacts_id) ? $contacts_id:"",
            'contact_type_id'   => isset($contact_groupid) ? $contact_groupid:"",
            "campaign_name"     => $campaign_name,
            "campaign_message"  => str_replace(array("'",'"'),array('`','`'),$message),
            'manual_phone'      => $to_numbers,
            "posting_status"    => $posting_status, 
            "schedule_time"     => $schedule_time,
            "report"            => json_encode($report),
            "time_zone"         => $time_zone,
            "total_thread"      => $thread,
            "successfully_sent" => $successfully_sent,
            "created_at"        => $added_at,
            'user_gender'       => isset($user_gender) ? $user_gender:"",
            'user_time_zone'    => isset($user_time_zone) ? $user_time_zone:"",
            'user_locale'       => isset($user_locale) ? $user_locale:""
        );

        if(!empty($label_ids)) 
            $inserted_data['label_ids'] = implode(',', $label_ids); 
        else 
            $inserted_data['label_ids'] ="";

        if(!empty($excluded_label_ids)) 
            $inserted_data['excluded_label_ids'] = implode(',', $excluded_label_ids); 
        else 
            $inserted_data['excluded_label_ids'] = "";

        $fb_label_names = array();
        if(!empty($label_ids))
        {
            $fb_label_data=$this->basic->get_data("messenger_bot_broadcast_contact_group",array("where_in"=>array("id"=>$label_ids)));
            foreach ($fb_label_data as $key => $value) 
            {
               if($value['invisible']=='0')
                $fb_label_names[]=$value["group_name"];
            }  
        }

        $inserted_data['label_names'] = implode(',', $fb_label_names);

        $status=$this->_check_usage($module_id=264,$request=$thread);
        if($status=="3")  //monthly limit is exceeded, can not send another ,message this month
        {
            echo json_encode(array('status'=>'0','message'=>$this->lang->line("Sorry, your monthly limit to send SMS is exceeded.")));
            exit();
        }


        if($this->basic->insert_data("sms_sending_campaign", $inserted_data))
        {
            // getting inserted row id
            $campaign_id = $this->db->insert_id();

            $report_insert = array();
            foreach ($report as $key=>$value) 
            {
                $report_insert = array(
                    'user_id'              => $this->user_id,
                    'sms_api_id'           => $value['api_id'],
                    'campaign_id'          => $campaign_id,
                    'contact_id'           => $value['contact_id'],
                    'subscriber_id'        => $value['subscriber_id'],
                    'contact_first_name'   => $value['contact_first_name'],
                    'contact_last_name'    => $value['contact_last_name'],
                    'contact_email'        => $value['contact_email'],
                    'contact_phone_number' => $key,
                    'sent_time'            => '',
                    'delivery_id'          => 'pending',
                    'processed'            => '0'
                );
                
                $this->basic->insert_data("sms_sending_campaign_send", $report_insert);
            }

            $this->_insert_usage_log($module_id=264,$request=$thread);

            echo json_encode(array('status'=>'1'));
        } else
        {
            echo json_encode(array('status'=>'0','message'=>$this->lang->line('Something went wrong, please try once again.')));
        }
    }

    public function edit_sms_campaign($id=0)
    {
        if($this->session->userdata('user_type') != 'Admin' && !in_array(264,$this->module_access)) redirect('home/login_page', 'location');

        if($id==0) exit();

        $data['body']          = "sms_email_manager/sms/edit_sms_campaigns";
        $data["time_zone"]     = $this->_time_zone_list();
        $data["campaign_data"] = $this->basic->get_data("sms_sending_campaign",array("where"=>array("id"=>$id,"user_id"=>$this->user_id)));
        $data['selected_contact_gorups'] = explode(",",$data['campaign_data'][0]['contact_type_id']);

        if(!isset($data["campaign_data"][0]["posting_status"]) || $data["campaign_data"][0]["posting_status"]!='0' ) exit();

        $page_info = $this->basic->get_data("facebook_rx_fb_page_info",array("where"=>array("user_id"=>$this->user_id,"facebook_rx_fb_user_info_id"=>$this->session->userdata("facebook_rx_fb_user_info"),"bot_enabled"=>'1')),$select='',$join='',$limit='',$start=NULL,$order_by='page_name ASC');
        $data['page_info'] = $page_info;

        // only pending campaigns are editable
        if(!isset($data["campaign_data"][0]["posting_status"]) || $data["campaign_data"][0]["posting_status"]!='0' ) exit();

        // only scheduled campaigns can be editted
        if(!isset($data["campaign_data"][0]["time_zone"]) || $data["campaign_data"][0]["time_zone"]=='' ) exit();
        
        /**Get contact number and   sms_email_contact_group***/
        $user_id = $this->user_id;
        $table_type = ' sms_email_contact_group';   
        $where_type['where'] = array('user_id'=>$user_id);
        $info_type = $this->basic->get_data($table_type,$where_type,$select='', $join='', $limit='', $start='', $order_by='type');  
        $result = array();

        if(isset($info_type) && !empty($info_type))
        {
            foreach ($info_type as  $value) 
            {
                $search_key = $value['id'];
                $search_type = $value['type'];

                $where_simple = array('sms_email_contacts.user_id' => $this->user_id);
                $this->db->where("FIND_IN_SET('$search_key',sms_email_contacts.contact_type_id) !=", 0);
                $where = array('where'=>$where_simple);
                $this->db->select("count(sms_email_contacts.id) as number_count",false);    
                $contact_details = $this->basic->get_data('sms_email_contacts', $where, $select='', $join='', $limit='', $start='', $order_by='sms_email_contacts.first_name', $group_by='', $num_rows=0);
            
                foreach ($contact_details as $key2 => $value2) 
                {
                    if($value2['number_count']>0)
                    $group_name[$search_key] = $search_type." (".$value2['number_count'].")";
                }
                    
            }  
        }   

                                                        
        /***get sms config***/
        $apiAccess = $this->config->item('sms_api_access');
        if($this->config->item('sms_api_access') == "") $apiAccess = "0";

        if($apiAccess == '1' && $this->session->userdata("user_type") == 'Member')
        {
            $join = array('users' => 'sms_api_config.user_id=users.id,left');
            $select = array('sms_api_config.*','users.id AS usersId','users.user_type');
            $where_in = array('sms_api_config.user_id'=>array('1',$this->user_id),'users.user_type'=>array('Admin','User'));
            $where = array('where'=> array('sms_api_config.status'=>'1'),'where_in'=>$where_in);
            $sms_api_config=$this->basic->get_data('sms_api_config', $where, $select, $join, $limit='', $start='', $order_by='phone_number ASC', $group_by='', $num_rows=0);
        } else
        {
            $where = array("where" => array('user_id'=>$this->user_id,'status'=>'1'));
            $sms_api_config=$this->basic->get_data('sms_api_config', $where, $select='', $join='', $limit='', $start='', $order_by='phone_number ASC', $group_by='', $num_rows=0);
        }
        
        $sms_api_config_option = array();

        foreach ($sms_api_config as $info) {
            $id = $info['id'];
            if($info['phone_number'] != "")
                $sms_api_config_option[$id] = $info['gateway_name'].": ".$info['phone_number'];
            else
                $sms_api_config_option[$id] = $info['gateway_name'];
        }

        $data['locale_list']       = $this->sdk_locale();
        $data["time_zone_numeric"] = $this->_time_zone_list_numeric();
        $data['sms_option']        = $sms_api_config_option;
        $data['groups_name']       = isset($group_name) ? $group_name: "";
        $data['page_title']        = $this->lang->line('Edit SMS Campaign');

        $this->_viewcontroller($data);   
    }

    public function edit_sms_campaign_action()
    {
        if($this->session->userdata('user_type') != 'Admin' && !in_array(264,$this->module_access)) exit;

        if(!$_POST) exit();

        $report = array();

        $campaign_id   = $this->input->post('campaign_id',true);
        $previous_thread = $this->input->post("previous_thread");
        $schedule_name = strip_tags(trim($this->input->post('campaign_name', true)));
        $message       = $this->input->post('message', true);
        $schedule_time = $this->input->post('schedule_time');
        $time_zone     = strip_tags(trim($this->input->post('time_zone', true)));
        $sms_api       = strip_tags(trim($this->input->post('from_sms', true)));
        $to_numbers    = trim($this->input->post('to_numbers', true));
        $country_code_add  = trim($this->input->post('country_code_add', true));
        $country_code_remove  = trim($this->input->post('country_code_remove', true));

        $page_auto_id = $this->input->post('page',true); // page auto id
        $label_ids = $this->input->post('label_ids',true);
        $excluded_label_ids = $this->input->post('excluded_label_ids',true);
        $user_gender = $this->input->post('user_gender',true);
        $user_time_zone = $this->input->post('user_time_zone',true);
        $user_locale = $this->input->post('user_locale',true);

        if(!isset($label_ids) || !is_array($label_ids)) $label_ids=array();
        if(!isset($excluded_label_ids) || !is_array($excluded_label_ids)) $excluded_label_ids=array();

        if($time_zone=='') $time_zone = "Asia/Novosibirsk";

        $successfully_sent  = 0;
        $added_at           = date("Y-m-d H:i:s");
        $posting_status     = "0";

        // Messenger Subscriber Section Started
        if(isset($page_auto_id) && !empty($page_table_id))
        {
            $pageinfo = $this->basic->get_data("facebook_rx_fb_page_info",array("where"=>array("id"=>$page_auto_id,"user_id"=>$this->user_id)));
            if(!isset($pageinfo[0]))
            {
                echo json_encode(array('status'=>'0','message'=>$this->lang->line("Something went wrong.")));
                exit();
            }
            $fb_page_id  = $pageinfo[0]['page_id'];
            $page_name  = $pageinfo[0]['page_name'];

            $excluded_label_ids_temp=$excluded_label_ids;
            $unsubscribe_labeldata=$this->basic->get_data("messenger_bot_broadcast_contact_group",array("where"=>array("user_id"=>$this->user_id,"page_id"=>$page_auto_id,"unsubscribe"=>"1")));
            foreach ($unsubscribe_labeldata as $key => $value) 
            {
                array_push($excluded_label_ids_temp, $value["id"]);
            }

            if(count($label_ids)>0) $sql_part="("; else $sql_part="";        
            $sql_part_array=array();
            foreach ($label_ids as $key => $value) 
            {
               $sql_part_array[]="FIND_IN_SET('".$value."',contact_group_id) !=0";
            }        
            if(count($label_ids)>0) 
            {
                $sql_part.=implode(' OR ', $sql_part_array);
                $sql_part.=") AND ";
            }

            $sql_part2="";
            $sql_part_array2=array();
            foreach ($excluded_label_ids_temp as $key => $value) 
            {
              $sql_part_array2[]="NOT FIND_IN_SET('".$value."',contact_group_id) !=0";          
            }        
            if(count($excluded_label_ids_temp)>0) 
            {
                $sql_part2=implode(' AND ', $sql_part_array2);
                $sql_part2.=" AND ";
            }

            $sql_part3="";
            $sql_part_array3 = array();
            if($user_gender!='') $sql_part_array3[] = "gender = '{$user_gender}'";
            if($user_time_zone!='') $sql_part_array3[] = "timezone = '{$user_time_zone}'";
            if($user_locale!='') $sql_part_array3[] = "locale = '{$user_locale}'";

            if(count($sql_part_array3)>0) 
            {
                $sql_part3 = implode(' AND ', $sql_part_array3);
                $sql_part3 .=" AND ";
            }

            $sql="SELECT * FROM messenger_bot_subscriber WHERE ".$sql_part." ".$sql_part2." ".$sql_part3." user_id = ".$this->user_id." AND page_table_id = {$page_auto_id} AND is_bot_subscriber='1' AND phone_number!='' AND permission='1' AND unavailable_conversation='0';";
            $lead_list = $this->basic->execute_query($sql);

            if(isset($lead_list) && !empty($lead_list)){
                foreach ($lead_list as $lead_key => $lead_value) {

                    if($lead_value['phone_number'] == "") continue;

                    if(isset($country_code_add) & $country_code_add != '')
                    {
                        if(!preg_match("/^\+?{$country_code_add}/",$lead_value['phone_number'])) 
                        {
                            $lead_value['phone_number'] = $country_code_add.$lead_value['phone_number'];
                        }
                    }
                    else if(isset($country_code_remove) && $country_code_remove != '')
                    {
                        // $lead_value['phone_number'] = preg_replace("/^\+?{$country_code_remove}/", '',$lead_value['phone_number']);
                        if(preg_match("/^\+?{$country_code_remove}/",$lead_value['phone_number'])) {
                            $lead_value['phone_number'] = preg_replace("/^\+?{$country_code_remove}/",'',$lead_value['phone_number']);
                        }
                    }

                    $report[$lead_value['phone_number']] = array(
                        'api_id'              => $sms_api,
                        'contact_id'          => '0',
                        'subscriber_id'       => $lead_value['id'],
                        'contact_first_name'  => isset($lead_value['first_name']) ? $lead_value['first_name']:"",
                        'contact_last_name'   => isset($lead_value['last_name']) ? $lead_value['last_name']:"",
                        'contact_email'       => isset($lead_value['email']) ? $lead_value['email']:"",
                        'contact_phone_number'=> isset($lead_value['phone_number']) ? $lead_value['phone_number']:"",
                        'sent_time'           =>'pending',
                        'delivery_id'         =>'pending',
                    );
                }
            }
        }
        // Messenger Subscriber Section Ended

        $contacts_sms_group = $this->input->post('contacts_id', true);
        if(isset($contacts_sms_group) && !empty($contacts_sms_group))
            $contact_groupid    = implode(",",$contacts_sms_group);

        if(!empty($to_numbers))
        {
            $exploded_to_numbers = explode(',',$to_numbers);
            $exploded_to_numbers = array_unique($exploded_to_numbers);
        }

        $manual_numbers = array();
        $contacts_id = array();
        $total_user = array();

        if(isset($contacts_sms_group) && !empty($contacts_sms_group)){
            foreach ($contacts_sms_group as $key => $value) 
            {
                $where_simple = array('sms_email_contacts.user_id'=>$this->user_id);
                $this->db->where("FIND_IN_SET('$value',sms_email_contacts.contact_type_id) !=", 0);
                $where = array('where'=>$where_simple);

                $contact_details = $this->basic->get_data('sms_email_contacts', $where, $select='');
                foreach ($contact_details as $key2 => $value2) 
                {
                    if($value2['phone_number'] == "") continue;

                    if(isset($country_code_add) & $country_code_add != '')
                    {
                        if(!preg_match("/^\+?{$country_code_add}/",$value2['phone_number'])) 
                        {
                            $value2['phone_number'] = $country_code_add.$value2['phone_number'];
                        }
                    }
                    else if(isset($country_code_remove) && $country_code_remove != '')
                    {
                        if(preg_match("/^\+?{$country_code_remove}/",$value2['phone_number'])) 
                        {
                            $value2['phone_number'] = preg_replace("/^\+?{$country_code_remove}/",'',$value2['phone_number']);
                        }

                    }

                    $fullname = $value2['first_name']." ".$value2['last_name'];

                    $report[$value2['phone_number']] = array(
                        'api_id'               => $sms_api,
                        'contact_id'           => $value2['id'],
                        'subscriber_id'        => "0",
                        'contact_first_name'   => isset($value2['first_name']) ? $value2['first_name']:"",
                        'contact_last_name'    => isset($value2['last_name']) ? $value2['last_name']:"",
                        'contact_email'        => isset($value2['email']) ? $value2['email']:"",
                        'contact_phone_number' => isset($value2['phone_number']) ? $value2['phone_number']:"",
                        'sent_time'            =>'pending',
                        'delivery_id'          =>'pending',
                    );
                  
                    $contacts_id[] = isset($value2["id"]) ? $value2["id"]: "";
                }
            }
        }
        
        // for manual phone number insertion into report
        $manual_thread = 0;
        if(isset($exploded_to_numbers))
        {
            foreach ($exploded_to_numbers as $single_values) 
            {
                if(isset($country_code_add) & $country_code_add != '')
                {
                    if(preg_match("/^\+?{$country_code_add}/",$single_values)) 
                    {
                        $single_values = $single_values;
                    }
                    else
                    { 
                        $single_values = $country_code_add.$single_values;
                    }

                }
                else if(isset($country_code_remove) && $country_code_remove != '')
                {
                    // $single_values = preg_replace("/^\+?{$country_code_remove}/", '',$single_values);
                    if(preg_match("/^\+?{$country_code_remove}/",$single_values)) {
                        $single_values = preg_replace("/^\+?{$country_code_remove}/",'',$single_values);
                    }
                    else
                    { 
                        $single_values = $single_values;
                    }

                } else
                {
                    $single_values = $single_values;
                }

                $report[$single_values] = array(
                    'api_id'            => $sms_api,
                    'contact_id'        => "0",
                    'subscriber_id'     => "0",
                    'contact_first_name'=> "",
                    'contact_username'  => "",
                    'contact_email'     => "",
                    'contact_phone_number'=>$single_values,
                    'sent_time' =>'pending',
                    'delivery_id' =>'pending',
                );

                $manual_thread++;
            }
        }

        $contacts_id = array_filter($contacts_id);
        $contacts_id = array_unique($contacts_id);
        $contacts_id = implode(',', $contacts_id);

        $thread = count($report);

        if($thread==0)
        {
            echo json_encode(array('status'=>'0','message'=>$this->lang->line("Campaign could not target any subscriber with phone number to reach message. Please try again with different targeting options.")));
            exit();
        }

        // updating data of sms_campaign_campaign Table
        $updated_data = array(
            "user_id"           => $this->user_id,
            "api_id"            => $sms_api,
            'page_id'           => isset($page_auto_id) ? $page_auto_id:"",
            'fb_page_id'        => isset($fb_page_id) ? $fb_page_id:"", 
            'page_name'         => isset($page_name) ? $page_name:"",
            "contact_ids"       => isset($contacts_id) ? $contacts_id:"",
            'contact_type_id'   => isset($contact_groupid) ? $contact_groupid:"",
            "campaign_name"     => $schedule_name,
            "campaign_message"  => str_replace(array("'",'"'),array('`','`'),$message),
            'manual_phone'      => $to_numbers,
            "posting_status"    => $posting_status, 
            "schedule_time"     => $schedule_time,
            "report"            => json_encode($report),
            "time_zone"         => $time_zone,
            "total_thread"      => $thread,
            "successfully_sent" => $successfully_sent,
            "created_at"        => $added_at,
            'user_gender'       => isset($user_gender) ? $user_gender:"",
            'user_time_zone'    => isset($user_time_zone) ? $user_time_zone:"",
            'user_locale'       => isset($user_locale) ? $user_locale:""
        );

        if(!empty($label_ids)) 
            $updated_data['label_ids'] = implode(',', $label_ids); 
        else 
            $updated_data['label_ids'] ="";

        if(!empty($excluded_label_ids)) 
            $updated_data['excluded_label_ids'] = implode(',', $excluded_label_ids); 
        else 
            $updated_data['excluded_label_ids'] = "";

        $fb_label_names = array();
        if(!empty($label_ids))
        {
            $fb_label_data = $this->basic->get_data("messenger_bot_broadcast_contact_group",array("where_in"=>array("id"=>$label_ids)));
            foreach ($fb_label_data as $key => $value) 
            {
               if($value['invisible']=='0')
               $fb_label_names[]=$value["group_name"];
            }  
        }

        $updated_data['label_names'] = implode(',', $fb_label_names);

        $current_total_thread = $previous_thread - $thread;
        $current_total_thread_abs = abs($current_total_thread);
        if($current_total_thread<0)
        {
            $status=$this->_check_usage($module_id=264,$request=$current_total_thread_abs);
             if($status=="3")  //monthly limit is exceeded, can not send another ,message this month
            {
                echo json_encode(array('status'=>'0','message'=>$this->lang->line("Sorry, your monthly limit to send SMS is exceeded.")));
                exit();
            }
        }

        /* updating sms_sending_campaign table data of the campaign */
        if($this->basic->update_data("sms_sending_campaign", array("id" => $campaign_id,"user_id"=>$this->user_id), $updated_data))
        {
            /* Delete the rows of updated campaign from sms_sending_campaign_send table */
            $this->basic->delete_data("sms_sending_campaign_send", array("campaign_id" =>$campaign_id));

            $report_insert = array();
            foreach ($report as $key=>$value) 
            {
                $report_insert = array(
                    'user_id'              => $this->user_id,
                    'sms_api_id'           => $value['api_id'],
                    'campaign_id'          => $campaign_id,
                    'contact_id'           => $value['contact_id'],
                    'subscriber_id'        => $value['subscriber_id'],
                    'contact_first_name'   => $value['contact_first_name'],
                    'contact_last_name'    => $value['contact_last_name'],
                    'contact_email'        => $value['contact_email'],
                    'contact_phone_number' => $key,
                    'delivery_id'          => 'pending',
                    'sent_time'            => '',
                    'processed'            => '0'
                );

                /* Inserting again the updated report data into sms_sending_campaign_send table */
                $this->basic->insert_data("sms_sending_campaign_send", $report_insert);
            }

            if($current_total_thread<0){
                $this->_insert_usage_log($module_id=264,$request=$current_total_thread_abs);
            }
            else {
                $this->_delete_usage_log($module_id=264,$request=$current_total_thread_abs);
            }

            echo json_encode(array('status'=>'1'));
        } else
        {
            echo json_encode(array('status'=>'0','message'=>$this->lang->line('Something went wrong, please try once again.')));
        }
    }

    // ===========================================================================================================
    //                                             Email Section                                                        
    // ============================================================================================================

    public function email_campaign_lists()
    {
        if($this->session->userdata('user_type') != 'Admin' && !in_array(263,$this->module_access)) redirect('home/login_page', 'location');

        $data['body'] = 'sms_email_manager/email/email_campaign/email_campaign_lists';
        $data['page_title'] = $this->lang->line('Email Campaign');
        $this->_viewcontroller($data);
    }

    public function email_campaign_lists_data()
    {
        if($this->session->userdata('user_type') != 'Admin' && !in_array(263,$this->module_access)) exit;

        $this->ajax_check();

        $campaign_status     = trim($this->input->post("campaign_status",true));
        $searching_campaign  = trim($this->input->post("searching_campaign",true));
        $post_date_range = $this->input->post("post_date_range",true);

        $display_columns = array("#",'id','campaign_name','email_api','sent_count','actions','posting_status','schedule_time','created_at');

        $page = isset($_POST['page']) ? intval($_POST['page']) : 1;
        $start = isset($_POST['start']) ? intval($_POST['start']) : 0;
        $limit = isset($_POST['length']) ? intval($_POST['length']) : 10;
        $sort_index = isset($_POST['order'][0]['column']) ? strval($_POST['order'][0]['column']) : 1;
        $sort = isset($display_columns[$sort_index]) ? $display_columns[$sort_index] : 'id';
        $order = isset($_POST['order'][0]['dir']) ? strval($_POST['order'][0]['dir']) : 'desc';
        $order_by=$sort." ".$order;

        $where_simple=array();
        $where_simple['user_id'] = $this->user_id;

        if($post_date_range!="")
        {
            $exp = explode('|', $post_date_range);
            $from_date = isset($exp[0])?$exp[0]:"";
            $to_date   = isset($exp[1])?$exp[1]:"";

            if($from_date!="Invalid date" && $to_date!="Invalid date")
            {
                $from_date = date('Y-m-d', strtotime($from_date));
                $to_date   = date('Y-m-d', strtotime($to_date));
                $where_simple["Date_Format(created_at,'%Y-%m-%d') >="] = $from_date;
                $where_simple["Date_Format(created_at,'%Y-%m-%d') <="] = $to_date;
            }
        }

        if($searching_campaign !="") $where_simple['campaign_name like'] = "%".$searching_campaign."%";
        if($campaign_status !="") $where_simple['posting_status'] = $campaign_status;

        $where  = array('where'=>$where_simple);

        $table = "email_sending_campaign";
        $info = $this->basic->get_data($table,$where,$select='',$join='',$limit,$start,$order_by,$group_by='');

        $total_rows_array = $this->basic->count_row($table,$where,$count="id",$join,$group_by='');
        $total_result = $total_rows_array[0]['total_rows'];

        for($i = 0; $i < count($info); $i++)
        {
            $action_count = 3;
            $posting_status = $info[$i]['posting_status'];

            if($info[$i]['schedule_time'] != "0000-00-00 00:00:00")
                $info[$i]['schedule_time'] = "<div style='min-width:100px !important;'>".date("M j, y H:i",strtotime($info[$i]['schedule_time']))."</div>";
            else 
                $info[$i]['schedule_time'] = "<div style='min-width:100px !important;' class='text-muted'><i class='fas fa-exclamation-circle'></i> ".$this->lang->line('Not Scheduled')."</div>";

            $email_api_infos = $this->basic->get_data($info[$i]['configure_email_table'],array('where'=>array('id'=>$info[$i]['api_id'])));

            if($info[$i]['configure_email_table'] == 'email_smtp_config')
                $info[$i]['email_api'] = "SMTP - ".$email_api_infos[0]['email_address'];
            if($info[$i]['configure_email_table'] == 'email_mailgun_config')
                $info[$i]['email_api'] = "Mailgun - ".$email_api_infos[0]['email_address'];
            if($info[$i]['configure_email_table'] == 'email_mandrill_config')
                $info[$i]['email_api'] = "Mandrill - ".$email_api_infos[0]['email_address'];
            if($info[$i]['configure_email_table'] == 'email_sendgrid_config')
                $info[$i]['email_api'] = "Sendgrid - ".$email_api_infos[0]['email_address'];

            if(isset($info[$i]['email_attachment']) && $info[$i]['email_attachment'] != '')
            {
                $action_count++;
                $attachment = "<a target='_BLANK' href='".base_url('sms_email_manager/download_email_attachment/').$info[$i]['email_attachment']."/".$this->user_id."' data-toggle='tooltip' title='".$this->lang->line("Attachment")."' class='btn btn-circle btn-outline-info'><i class='fas fa-paperclip'></i></a>";
            } else
            {
                $attachment = "";
            }

            // added date
            if($info[$i]['created_at'] != "0000-00-00 00:00:00")
                $info[$i]['created_at'] = "<div style='min-width:100px !important;'>".date("M j, y H:i",strtotime($info[$i]['created_at']))."</div>";

            // generating delete button
            if($posting_status=='1')
                $delete_btn = "<a href='#' class='btn btn-circle btn-light pointer text-muted' data-toggle='tooltip' title='".$this->lang->line("Campaign in processing can not be deleted. You can pause campaign and then delete it.")."'><i class='fa fa-trash'></i></a>";
            else 
                $delete_btn =  "<a href='#' data-toggle='tooltip' title='".$this->lang->line("delete campaign")."' id='".$info[$i]['id']."' class='delete_email_campaign btn btn-circle btn-outline-danger'><i class='fa fa-trash'></i></a>";

            $is_try_again = $info[$i]["is_try_again"];

            $force_porcess_str="";

            // generating restat and force processing button
            $number_of_email_to_be_sent_in_try = $this->config->item("number_of_email_to_be_sent_in_try");
            if($number_of_email_to_be_sent_in_try == "") $number_of_email_to_be_sent_in_try = 10;

            if($number_of_email_to_be_sent_in_try == "" || $number_of_email_to_be_sent_in_try == "0")
            {
                $force_porcess_str="";
            }
            else
            {
                $action_count++;
                if($posting_status=='1' && $is_try_again=='1')
                    $force_porcess_str .= "<a href='#' class='btn btn-circle btn-outline-dark pause_email_campaign_info' table_id='".$info[$i]['id']."' data-toggle='tooltip' title='".$this->lang->line("Pause Campaign")."'><i class='fas fa-pause'></i></a>";
                if($posting_status=='3')
                    $force_porcess_str .= "<a href='#' class='btn btn-circle btn-outline-success play_email_campaign_info' table_id='".$info[$i]['id']."' data-toggle='tooltip' title='".$this->lang->line("Start Campaign")."'><i class='fas fa-play'></i></a>";
            }

            if($posting_status=='1'){
                $action_count++;
                $force_porcess_str .= "<a href='#' id='".$info[$i]['id']."' class='force_email btn btn-circle btn-outline-warning' data-toggle='tooltip' title='".$this->lang->line("force reprocessing")."'><i class='fas fa-sync'></i></a>";
            }

            // status
            if( $posting_status == '2') 
                $info[$i]['posting_status'] = '<div style="min-width:100px"><span class="text-success badge"><i class="fas fa-check-circle"></i> '.$this->lang->line("Completed").'</span></div>';
            else if( $posting_status == '1') 
                $info[$i]['posting_status'] = '<div style="min-width:100px"><span class="text-warning"><i class="fas fa-spinner"></i> '.$this->lang->line("Processing").'</span></div>';
            else if( $posting_status == '3') 
                $info[$i]['posting_status'] = '<div style="min-width:100px"><span class="text-muted"><i class="fas fa-stop"></i> '.$this->lang->line("Paused").'</span></div>';
            else 
                $info[$i]['posting_status'] = '<div style="min-width:100px"><span class="text-danger"><i class="far fa-times-circle"></i> '.$this->lang->line("Pending").'</span></div>';

            // sent column
            $info[$i]["sent_count"] =  $info[$i]["successfully_sent"]."/". $info[$i]["total_thread"] ;

            $report_btn = "<a href='#' class='campaign_report btn btn-circle btn-outline-primary' data-toggle='tooltip' title='".$this->lang->line("View Report")."' 
                table_id='".$info[$i]['id']."' 
                campaign_name='".$info[$i]['campaign_name']."' 
                campaign_message='".$info[$i]['email_message']."'
                email_api='".$info[$i]['email_api']."' 
                campaign_status='".$posting_status."' 
                successfullysent='".$info[$i]["successfully_sent"]."' 
                totalThread='".$info[$i]["total_thread"]."' 
                ><i class='fas fa-eye'></i> </a>";

            if($posting_status != '0' || $info[$i]['time_zone'] == "") 
                $edit_btn = "<a href='#' data-toggle='tooltip' title='".$this->lang->line("only pending campaigns are editable")."' class='btn btn-circle btn-light'><i class='fas fa-edit'></i></a>";
            else
            {
                $edit_url = site_url('sms_email_manager/edit_email_campaign/'.$info[$i]['id']);
                $edit_btn =  "<a data-toggle='tooltip' title='".$this->lang->line('edit campaign')."' href='".$edit_url."' class='btn btn-circle btn-outline-warning'><i class='fas fa-edit'></i></a>";
            }

            $action_width = ($action_count*47)+20;
            $info[$i]['actions'] ='
            <div class="dropdown d-inline dropright">
              <button class="btn btn-outline-primary dropdown-toggle no_caret" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                <i class="fa fa-briefcase"></i>
              </button>
              <div class="dropdown-menu mini_dropdown text-center" style="width:'.$action_width.'px !important">';
                $info[$i]['actions'] .= $report_btn;
                $info[$i]['actions'] .= $edit_btn;
                $info[$i]['actions'] .= $force_porcess_str;
                if(isset($attachment) && !empty($attachment))
                    $info[$i]['actions'] .= $attachment;

                $info[$i]['actions'] .= $delete_btn;
                $info[$i]['actions'] .="
              </div>
            </div>
            <script>
            $('[data-toggle=\"tooltip\"]').tooltip();</script>";
        }

        $data['draw'] = (int)$_POST['draw'] + 1;
        $data['recordsTotal'] = $total_result;
        $data['recordsFiltered'] = $total_result;
        $data['data'] = convertDataTableResult($info, $display_columns ,$start,$primary_key="id");

        echo json_encode($data);
    }

    public function ajax_attachment_upload()
    {
       if($this->session->userdata('user_type') != 'Admin' && !in_array(263,$this->module_access)) exit;

       if ($_SERVER['REQUEST_METHOD'] === 'GET') exit();

       $ret = array();
       $output_dir = FCPATH."upload/attachment/";

       if(!file_exists($output_dir))
       {
           mkdir($output_dir,0777,true);
       }

       if (isset($_FILES["file"])) {

           $error = $_FILES["file"]["error"];

           $post_fileName = $_FILES["file"]["name"];
           $post_fileName_array = explode(".", $post_fileName);
           $ext = array_pop($post_fileName_array);
           $filename = implode('.', $post_fileName_array);
           $filename = $filename."_".$this->user_id."_".time().substr(uniqid(mt_rand(), true), 0, 6).".".$ext;

           $allow = ".png,.jpg,.jpeg,docx,.txt,.pdf,.ppt,.zip,.avi,.mp4,.mkv,.wmv,.mp3";
           $allow = str_replace('.', '', $allow);
           $allow = explode(',', $allow);
           if(!in_array(strtolower($ext), $allow)) 
           {
               echo json_encode("Are you kidding???");
               exit;
           }

           move_uploaded_file($_FILES["file"]["tmp_name"], $output_dir.'/'.$filename);
           $ret[]= $filename;
           $this->session->set_userdata("attachment_file_path_name_scheduler", $output_dir.'/'.$filename);
           $this->session->set_userdata("attachment_filename_scheduler", $filename);
           echo json_encode($filename);
       } 
    }

    public function delete_uploaded_attachment_file()
    {
        if($this->session->userdata('user_type') != 'Admin' && !in_array(263,$this->module_access)) exit;
        if(!$_POST) exit();

        $output_dir = FCPATH."upload/attachment/";
        if(isset($_POST["op"]) && $_POST["op"] == "delete" && isset($_POST['name']))
        {
            $fileName = $_POST['name'];
            $fileName = str_replace("..",".",$fileName); //required. if somebody is trying parent folder files
            $filePath = $output_dir. $fileName;
            if (file_exists($filePath))
            {
            unlink($filePath);
            }
        }
    }

    public function download_email_attachment($filename=0,$userid)
    {
        if($this->session->userdata('user_type') != 'Admin' && !in_array(263,$this->module_access)) exit;

        $user_id = $this->user_id;

        if($user_id != $userid) redirect('home/access_forbidden', 'location');

        $this->load->helper('download');
        $name = $filename;

        $fileDir = FCPATH.'upload/attachment/'.$filename;
        if(file_exists($fileDir))
        {
            $data = file_get_contents(FCPATH.'upload/attachment/'.$filename); 
            force_download($name, $data);
        } else
        {
            $this->error_404();
        }
    }

    public function delete_email_campaign()
    {
        if($this->session->userdata('user_type') != 'Admin' && !in_array(263,$this->module_access)) exit;

        $this->ajax_check();

        $id = $this->input->post("campaign_id");
        if($id == "" || $id=="0") exit;

        $file_data = $this->basic->get_data("email_sending_campaign",array('where'=>array('id'=>$id,'user_id'=>$this->user_id)));

        if($file_data[0]['email_attachment'] !="")
        {
            $file = FCPATH."upload/attachment/".$file_data[0]['email_attachment'];
            if(file_exists($file)) unlink($file);
        }

        if($this->basic->delete_data("email_sending_campaign",array("id"=>$id,"user_id"=>$this->user_id)))
        {
            if($this->basic->delete_data("email_sending_campaign_send",array("campaign_id"=>$id,"user_id"=>$this->user_id)))
                echo "1";
        }
        else 
            echo "0";
    }

    public function ajax_get_email_campaign_report_info()
    {
        if($this->session->userdata('user_type') != 'Admin' && !in_array(263,$this->module_access)) exit;

        $this->ajax_check();

        $table_id = $this->input->post('table_id');
        $searching = trim($this->input->post("searching",true));

        $display_columns = array("#","contact_first_name","contact_last_name","contact_email","contact_phone_number","sent_time","delivery_id");

        $page = isset($_POST['page']) ? intval($_POST['page']) : 1;
        $start = isset($_POST['start']) ? intval($_POST['start']) : 0;
        $limit = isset($_POST['length']) ? intval($_POST['length']) : 10;
        $sort_index = isset($_POST['order'][0]['column']) ? strval($_POST['order'][0]['column']) : 1;
        $sort = isset($display_columns[$sort_index]) ? $display_columns[$sort_index] : 'id';
        $order = isset($_POST['order'][0]['dir']) ? strval($_POST['order'][0]['dir']) : 'desc';
        $order_by=$sort." ".$order;

        $where['where'] = array('id'=> $table_id,'user_id'=>$this->user_id);

        $table="email_sending_campaign";
        $info = $this->basic->get_data($table,$where,$select='');

        if(isset($info[0]['report']) && $info[0]['report'] != '')
        {
            $campaign_details = $info[0];

            $report_info = json_decode($campaign_details['report'],true);
            $reply_info = $report_info;

            $reply_info = array_filter($reply_info, function($single_reply) use ($searching) 
            {
                if ($searching != '') {

                    if (stripos($single_reply['contact_first_name'], $searching) !== false || stripos($single_reply['contact_last_name'], $searching) !== false || stripos($single_reply['contact_email'], $searching) !== false || stripos($single_reply['contact_phone_number'], $searching) !== false) 
                    {
                        return TRUE; 
                    }
                    else
                    {
                        return FALSE;  
                    }
                }
                else
                {
                    return TRUE;
                }

            });


            usort($reply_info, function($first, $second) use ($sort, $order)
            {
                if ($first[$sort] == $second[$sort]) {
                    return 0;
                }
                else if ($first[$sort] > $second[$sort]) {
                    if ($order == 'desc') return 1;
                    else return -1;
                }
                else if ($first[$sort] < $second[$sort]) {
                    if ($order == 'desc') return -1;
                    else return 1;
                }

            });


            $final_info = array();
            $i = 0;
            $upper_limit = $start + $limit;

            foreach ($reply_info as $key => $value) { 

                if ($i >= $start && $i < ($upper_limit))
                    array_push($final_info, $value);

                $i++;
            }

            $result = array();
            foreach ($final_info as $value) {

                $temp = array();
                array_push($temp, ++$start);

                $fName = $value['contact_first_name'];
                $lName = $value['contact_last_name'];
                $contemail = $value['contact_email'];
                $sentime = $value['sent_time'];

                if($value['sent_time'] == "pending") $sentTime = "x";

                foreach ($value as $key => $column) 
                {
                    if($key=='contact_first_name' && $fName == "")
                        $column = "-";                    
                    if($key=='contact_last_name' && $lName == "")
                        $column = "-";                    
                    if($key=='contact_email' && $contemail == "")
                        $column = "-";

                    if ($key == 'sent_time')
                        $column = "<div style='min-width:100px;'>".date('M j, y H:i', strtotime($column))."</div>";                    

                    if ($key == 'sent_time' && isset($sentTime))
                        $column = 'x';

                    if($key=='delivery_id' && $column == 'pending')
                        $column = '<div style="min-width:100px"><span class="text-danger"><i class="fas fa-times"></i> '.ucfirst($column).'</span></div>';

                    if (in_array($key, $display_columns)) 
                        array_push($temp, $column);
                }

                array_push($result, $temp);
            }

        }
        else {

            $total_result = 0;
            $reply_info = array();
            $result = array();
        }

        $total_result = count($reply_info);
        $data['draw'] = (int)$_POST['draw'] + 1;
        $data['recordsTotal'] = $total_result;
        $data['recordsFiltered'] = $total_result;
        $data['data'] = $result;


        echo json_encode($data);
    }

    public function edit_email_campaign_content($id=0)
    {
        if($this->session->userdata('user_type') != 'Admin' && !in_array(263,$this->module_access)) redirect('home/login_page', 'location');
        if($id==0 || $id == "") exit();

        $data['body'] = "sms_email_manager/email/email_campaign/edit_email_campaign_message_content";
        $data['page_title'] = $this->lang->line("Edit Message Contents");
        $data["message_data"] = $this->basic->get_data("email_sending_campaign",array("where"=>array("id"=>$id,"user_id"=>$this->user_id)));
        $this->_viewcontroller($data);
    }

    public function edit_email_campaign_content_action()
    {
        if($this->session->userdata('user_type') != 'Admin' && !in_array(263,$this->module_access)) exit;

        $this->ajax_check();

        $table_id = $this->input->post("table_id",true);
        $user_id = $this->user_id;
        $message = $this->input->post("message");
        $message = str_replace(array("'",'"'), array('`','`'), $message);
        $edited_message   = array('email_message' => $message);

        if($this->basic->update_data('email_sending_campaign',array("id"=>$table_id,"user_id"=>$this->user_id),$edited_message))
        {
            echo "1";
        } else
        {
            echo "0";
        }  
    }

    public function restart_email_campaign()
    {
        if($this->session->userdata('user_type') != 'Admin' && !in_array(263,$this->module_access)) exit;
        $this->ajax_check();
        $id = $this->input->post("table_id");

        $where = array('id'=>$id,'user_id'=>$this->user_id);
        $data = array('is_try_again'=>'1','posting_status'=>'1');
        $this->basic->update_data('email_sending_campaign',$where,$data);
        echo '1';
    }

    public function ajax_email_campaign_pause()
    {
        if($this->session->userdata('user_type') != 'Admin' && !in_array(263,$this->module_access)) exit;
        $this->ajax_check();
        $table_id = $this->input->post('table_id');
        $post_info = $this->basic->update_data('email_sending_campaign',array('id'=>$table_id),array('posting_status'=>'3','is_try_again'=>'0'));
        echo '1';
        
    }

    public function ajax_email_campaign_play()
    {
        if($this->session->userdata('user_type') != 'Admin' && !in_array(264,$this->module_access)) exit;
        $this->ajax_check();
        $table_id = $this->input->post('table_id');
        $post_info = $this->basic->update_data('email_sending_campaign',array('id'=>$table_id),array('posting_status'=>'1','is_try_again'=>'1'));
        echo '1';
    }

    public function force_reprocess_email_campaign()
    {
        if($this->session->userdata('user_type') != 'Admin' && !in_array(263,$this->module_access)) exit;
        $this->ajax_check();
        $id = $this->input->post("id");

        $where = array('id'=>$id,'user_id'=>$this->user_id);
        $data = array('is_try_again'=>'1','posting_status'=>'1');
        $this->basic->update_data('email_sending_campaign',$where,$data);
        if($this->db->affected_rows() != 0) echo "1";
        else  echo "0";
    }

    public function create_email_campaign()
    {
        if($this->session->userdata('user_type') != 'Admin' && !in_array(263,$this->module_access)) redirect('home/login_page', 'location');

        $this->session->unset_userdata("attachment_file_path_name_scheduler");
        $this->session->unset_userdata("attachment_filename_scheduler");
        
        /**Get contact number and sms_email_contact_group***/
        $user_id = $this->user_id;
        $table_type = 'sms_email_contact_group';   
        $where_type['where'] = array('user_id'=>$user_id);
        $info_type = $this->basic->get_data($table_type,$where_type,$select='', $join='', $limit='', $start='', $order_by='type');

        $page_info = $this->basic->get_data("facebook_rx_fb_page_info",array("where"=>array("user_id"=>$user_id,"facebook_rx_fb_user_info_id"=>$this->session->userdata("facebook_rx_fb_user_info"),"bot_enabled"=>'1')),$select='',$join='',$limit='',$start=NULL,$order_by='page_name ASC');
        $data['page_info'] = $page_info; 
        $result = array();

        foreach ($info_type as  $value) 
        {
            $search_key = $value['id'];
            $search_type = $value['type'];

            $where_simple=array('sms_email_contacts.user_id'=>$this->user_id,'sms_email_contacts.unsubscribed'=>'0');
            $this->db->where("FIND_IN_SET('$search_key',sms_email_contacts.contact_type_id) !=", 0);
            $where=array('where'=>$where_simple);
            $this->db->select("count(sms_email_contacts.id) as number_count",false);    
            $contact_details=$this->basic->get_data('sms_email_contacts', $where, $select='', $join='', $limit='', $start='', $order_by=' sms_email_contacts.first_name', $group_by='', $num_rows=0);
        
            foreach ($contact_details as $key2 => $value2) 
            {
                if($value2['number_count']>0)
                $group_name[$search_key] = $search_type." (".$value2['number_count'].")";
            }
                
        }      
        
        $email_api_access = $this->config->item('email_api_access');
        if($this->config->item('email_api_access') == '') $email_api_access = '0';

        if($email_api_access == '1' && $this->session->userdata("user_type") == 'Member')
        {                                                            
            /***get smtp  option***/
            $join = array('users'=>'email_smtp_config.user_id=users.id,left');
            $select = array('email_smtp_config.*','users.id AS usersID','users.user_type');
            $where_in = array('email_smtp_config.user_id'=>array('1',$this->user_id),'users.user_type'=>array('Admin','Member'));
            $where = array('where'=> array('email_smtp_config.status'=>'1'),'where_in'=>$where_in);
            $smtp_info=$this->basic->get_data('email_smtp_config', $where, $select, $join, $limit='', $start='', $order_by='email_address ASC', $group_by='', $num_rows=0);
            
            $smtp_option=array();
            foreach ($smtp_info as $info) {
                $id="smtp_".$info['id'];
                $smtp_option[$id]="SMTP: ".$info['email_address'];
            }
            
            /***get mandrill option***/
            $join = array('users'=>'email_mandrill_config.user_id=users.id,left');
            $select = array('email_mandrill_config.*','users.id AS usersID','users.user_type');
            $where_in = array('email_mandrill_config.user_id'=>array('1',$this->user_id),'users.user_type'=>array('Admin','Member'));
            $where = array('where'=> array('email_mandrill_config.status'=>'1'),'where_in'=>$where_in);
            $smtp_info=$this->basic->get_data('email_mandrill_config', $where, $select, $join, $limit='', $start='', $order_by='email_address ASC', $group_by='', $num_rows=0);
            
            foreach ($smtp_info as $info) {
                $id="mandrill_".$info['id'];
                $smtp_option[$id]="Mandrill: ".$info['email_address'];
            }

            /***get sendgrid option***/
            $join = array('users'=>'email_sendgrid_config.user_id=users.id,left');
            $select = array('email_sendgrid_config.*','users.id AS usersID','users.user_type');
            $where_in = array('email_sendgrid_config.user_id'=>array('1',$this->user_id),'users.user_type'=>array('Admin','Member'));
            $where = array('where'=> array('email_sendgrid_config.status'=>'1'),'where_in'=>$where_in);
            $smtp_info=$this->basic->get_data('email_sendgrid_config', $where, $select, $join, $limit='', $start='', $order_by='email_address ASC', $group_by='', $num_rows=0);
            
            foreach ($smtp_info as $info) {
                $id="sendgrid_".$info['id'];
                $smtp_option[$id]="SendGrid: ".$info['email_address'];
            }

            /***get mailgun option***/
            $join = array('users'=>'email_mailgun_config.user_id=users.id,left');
            $select = array('email_mailgun_config.*','users.id AS usersID','users.user_type');
            $where_in = array('email_mailgun_config.user_id'=>array('1',$this->user_id),'users.user_type'=>array('Admin','Member'));
            $where = array('where'=> array('email_mailgun_config.status'=>'1'),'where_in'=>$where_in);
            $smtp_info=$this->basic->get_data('email_mailgun_config', $where, $select, $join, $limit='', $start='', $order_by='email_address ASC', $group_by='', $num_rows=0);
            
            foreach ($smtp_info as $info) {
                $id="mailgun_".$info['id'];
                $smtp_option[$id]="Mailgun: ".$info['email_address'];
            }

        } else
        {
            /***get smtp  option***/
            $where=array("where"=>array('user_id'=>$this->user_id,'status'=>'1'));
            $smtp_info=$this->basic->get_data('email_smtp_config', $where, $select='', $join='', $limit='', $start='', $order_by='email_address ASC', $group_by='', $num_rows=0);
            
            $smtp_option=array();
            foreach ($smtp_info as $info) {
                $id="smtp_".$info['id'];
                $smtp_option[$id]="SMTP: ".$info['email_address'];
            }
            
            /***get mandrill option***/
            $where=array("where"=>array('user_id'=>$this->user_id,'status'=>'1'));
            $smtp_info=$this->basic->get_data('email_mandrill_config', $where, $select='', $join='', $limit='', $start='', $order_by='email_address ASC', $group_by='', $num_rows=0);
            
            foreach ($smtp_info as $info) {
                $id="mandrill_".$info['id'];
                $smtp_option[$id]="Mandrill: ".$info['email_address'];
            }

            /***get sendgrid option***/
            $where=array("where"=>array('user_id'=>$this->user_id,'status'=>'1'));
            $smtp_info=$this->basic->get_data('email_sendgrid_config', $where, $select='', $join='', $limit='', $start='', $order_by='email_address ASC', $group_by='', $num_rows=0);
            
            foreach ($smtp_info as $info) {
                $id="sendgrid_".$info['id'];
                $smtp_option[$id]="SendGrid: ".$info['email_address'];
            }

            /***get mailgun option***/
            $where=array("where"=>array('user_id'=>$this->user_id,'status'=>'1'));
            $smtp_info=$this->basic->get_data('email_mailgun_config', $where, $select='', $join='', $limit='', $start='', $order_by='email_address ASC', $group_by='', $num_rows=0);
            
            foreach ($smtp_info as $info) {
                $id="mailgun_".$info['id'];
                $smtp_option[$id]="Mailgun: ".$info['email_address'];
            }
        }

        $data['email_option'] = $smtp_option;
        $data['groups_name'] = isset($group_name) ? $group_name:"";
        $data["time_zone"]   = $this->_time_zone_list();
        $data["time_zone_numeric"]= $this->_time_zone_list_numeric();
        $data['locale_list'] = $this->sdk_locale();
        $data['body']        = "sms_email_manager/email/email_campaign/create_email_campaign";
        $data['page_title']  = $this->lang->line('Create Email Campaign');
        $this->_viewcontroller($data);
    }

    public function create_email_campaign_action()
    {
        if($this->session->userdata('user_type') != 'Admin' && !in_array(263,$this->module_access)) exit;
        $this->ajax_check();

        if($this->is_demo == '1')
        {
            if($this->session->userdata('user_type') == "Admin")
            {                
                echo json_encode(array('status'=>'0','message'=>$this->lang->line("This action is disabled in this demo account. Please signup as user and try this with your account")));
                exit();
            }
        }

        $campaign_name        = strip_tags(trim($this->input->post('campaign_name', true)));
        $email_subject        = strip_tags(trim($this->input->post('email_subject', true)));
        $email_message        = $this->input->post('message');
        $from_email           = strip_tags(trim($this->input->post('from_email', true)));

        if(isset($from_email) && !empty($from_email))
            $from_email_separate  = explode('_', $from_email);

        $api_id               = $from_email_separate[1];
        $configure_table_name = "email_smtp_config";
        $schedule_time        = $this->input->post('schedule_time');
        $time_zone            = strip_tags(trim($this->input->post('time_zone', true)));
        $attachement          = $this->session->userdata("attachment_file_path_name_scheduler");
        $filename             = $this->session->userdata("attachment_filename_scheduler");

        $page_auto_id = $this->input->post('page',true); // page auto id
        $label_ids = $this->input->post('label_ids',true);
        $excluded_label_ids = $this->input->post('excluded_label_ids',true);
        $user_gender = $this->input->post('user_gender',true);
        $user_time_zone = $this->input->post('user_time_zone',true);
        $user_locale = $this->input->post('user_locale',true);

        if(!isset($label_ids) || !is_array($label_ids)) $label_ids=array();
        if(!isset($excluded_label_ids) || !is_array($excluded_label_ids)) $excluded_label_ids=array();

        if($time_zone=='')
        {
            $time_zone = "Europe/Dublin";
        }

        $this->session->unset_userdata("attachment_file_path_name_scheduler");
        $this->session->unset_userdata("attachment_filename_scheduler");

        if (strtolower($from_email_separate[0])=='mandrill') {
            $configure_table_name = "email_mandrill_config";
        } elseif (strtolower($from_email_separate[0])=='sendgrid') {
            $configure_table_name = "email_sendgrid_config";
        } elseif (strtolower($from_email_separate[0])=='mailgun') {
            $configure_table_name = "email_mailgun_config";
        }

        $successfully_sent = 0;
        $added_at          = date("Y-m-d H:i:s");
        $posting_status    = "0";

        // Messenger Subscriber Section Started
        if(isset($page_auto_id) && !empty($page_table_id))
        {
            $pageinfo = $this->basic->get_data("facebook_rx_fb_page_info",array("where"=>array("id"=>$page_auto_id,"user_id"=>$this->user_id)));
            if(!isset($pageinfo[0]))
            {
                echo json_encode(array('status'=>'0','message'=>$this->lang->line("Something went wrong.")));
                exit();
            }
            $fb_page_id  = $pageinfo[0]['page_id'];
            $page_name  = $pageinfo[0]['page_name'];

            $excluded_label_ids_temp=$excluded_label_ids;
            $unsubscribe_labeldata=$this->basic->get_data("messenger_bot_broadcast_contact_group",array("where"=>array("user_id"=>$this->user_id,"page_id"=>$page_auto_id,"unsubscribe"=>"1")));
            foreach ($unsubscribe_labeldata as $key => $value) 
            {
                array_push($excluded_label_ids_temp, $value["id"]);
            }

            if(count($label_ids)>0) $sql_part="("; else $sql_part="";        
            $sql_part_array=array();
            foreach ($label_ids as $key => $value) 
            {
               $sql_part_array[]="FIND_IN_SET('".$value."',contact_group_id) !=0";
            }        
            if(count($label_ids)>0) 
            {
                $sql_part.=implode(' OR ', $sql_part_array);
                $sql_part.=") AND ";
            }

            $sql_part2="";
            $sql_part_array2=array();
            foreach ($excluded_label_ids_temp as $key => $value) 
            {
              $sql_part_array2[]="NOT FIND_IN_SET('".$value."',contact_group_id) !=0";          
            }        
            if(count($excluded_label_ids_temp)>0) 
            {
                $sql_part2=implode(' AND ', $sql_part_array2);
                $sql_part2.=" AND ";
            }

            $sql_part3="";
            $sql_part_array3 = array();
            if($user_gender!='') $sql_part_array3[] = "gender = '{$user_gender}'";
            if($user_time_zone!='') $sql_part_array3[] = "timezone = '{$user_time_zone}'";
            if($user_locale!='') $sql_part_array3[] = "locale = '{$user_locale}'";

            if(count($sql_part_array3)>0) 
            {
                $sql_part3 = implode(' AND ', $sql_part_array3);
                $sql_part3 .=" AND ";
            }

            $sql="SELECT * FROM messenger_bot_subscriber WHERE ".$sql_part." ".$sql_part2." ".$sql_part3." user_id = ".$this->user_id." AND page_table_id = {$page_auto_id} AND is_bot_subscriber='1' AND email!='' AND permission='1' AND unavailable_conversation='0';";
            $lead_list = $this->basic->execute_query($sql);

            if(isset($lead_list) && !empty($lead_list)){
                foreach ($lead_list as $lead_key => $lead_value) {

                    $report[$lead_value['email']] = array(
                        'email_table_name'    => $configure_table_name,
                        'email_api_id'        => $api_id,
                        'contact_id'          => '0',
                        'subscriber_id'       => $lead_value['id'],
                        'contact_first_name'  => isset($lead_value['first_name']) ? $lead_value['first_name']:"",
                        'contact_last_name'   => isset($lead_value['last_name']) ? $lead_value['last_name']:"",
                        'contact_username'    => isset($lead_value['full_name']) ? $lead_value['full_name']:"",
                        'contact_email'       => isset($lead_value['email']) ? $lead_value['email']:"",
                        'contact_phone_number'=> isset($lead_value['phone_number']) ? $lead_value['phone_number']:"",
                        'sent_time'           =>'pending',
                        'delivery_id'         =>'pending',
                    );
                }
            }
        }
        // Messenger Subscriber Section Ended

        $contacts_email_group = $this->input->post('contacts_id', true);

        if(!is_array($contacts_email_group))
            $contacts_email_group=array();  


        if(isset($contacts_email_group) && !empty($contacts_email_group))
            $contact_groupid    = implode(",",$contacts_email_group); 

        $contacts_id = array();
        $report = array();

        if(isset($contacts_email_group) && !empty($contacts_email_group))
        foreach ($contacts_email_group as $key => $value) 
        {
            $where_simple = array('sms_email_contacts.user_id'=>$this->user_id,'sms_email_contacts.unsubscribed'=>'0');
            $this->db->where("FIND_IN_SET('$value',sms_email_contacts.contact_type_id) !=", 0);
            $where = array('where'=>$where_simple);    
            $contact_details=$this->basic->get_data('sms_email_contacts', $where);   
            foreach ($contact_details as $key2 => $value2) 
            {
                if($value2['email'] == "") continue;

                $fullname = $value2['first_name']." ".$value2['last_name'];

                $report[$value2['email']] = array(
                    'email_table_name'    => $configure_table_name,
                    'email_api_id'        => $api_id,
                    'contact_id'          => $value2['id'],
                    'subscriber_id'       => '0',
                    'contact_first_name'  => isset($value2['first_name']) ? $value2['first_name']:"",
                    'contact_last_name'   => isset($value2['last_name']) ? $value2['last_name']:"",
                    'contact_username'    => isset($fullname) ? $fullname:"",
                    'contact_email'       => isset($value2['email']) ? $value2['email']:"",
                    'contact_phone_number'=> isset($value2['phone_number']) ? $value2['phone_number']:"",
                    'sent_time'           =>'pending',
                    'delivery_id'         =>'pending',
                );

                $contacts_id[] = isset($value2["id"]) ? $value2["id"]: "";                
            }
        }


        $contacts_id = array_filter($contacts_id);
        $contacts_id = array_unique($contacts_id);
        $contacts_id = implode(',', $contacts_id);

        $thread = count($report);

        if($thread==0)
        {
            echo json_encode(array('status'=>'0','message'=>$this->lang->line("Campaign could not target any subscriber with email to reach message. Please try again with different targeting options.")));
            exit();
        }

        $inserted_data = array(
            "user_id"               => $this->user_id,
            "configure_email_table" => $configure_table_name,
            "api_id"                => $api_id,
            'page_id'               => isset($page_auto_id) ? $page_auto_id:"",
            'fb_page_id'            => isset($fb_page_id) ? $fb_page_id:"", 
            'page_name'             => isset($page_name) ? $page_name:"",
            "contact_ids"           => isset($contacts_id) ? $contacts_id:"",
            'contact_type_id'       => isset($contact_groupid) ? $contact_groupid:"",
            "campaign_name"         => $campaign_name,
            "email_subject"         => $email_subject,
            "email_message"         => str_replace(array("'",'"'),array("`","`"),$email_message),
            "email_attachment"      => isset($filename) ? $filename: "",
            "posting_status"        => $posting_status, 
            "schedule_time"         => $schedule_time,
            "time_zone"             => $time_zone,
            "total_thread"          => $thread,
            "successfully_sent"     => $successfully_sent,
            "created_at"            => $added_at,
            "report"                => json_encode($report),
            'user_gender'           => isset($user_gender) ? $user_gender:"",
            'user_time_zone'        => isset($user_time_zone) ? $user_time_zone:"",
            'user_locale'           => isset($user_locale) ? $user_locale:""
        );

        if(!empty($label_ids)) 
            $inserted_data['label_ids'] = implode(',', $label_ids); 
        else 
            $inserted_data['label_ids'] ="";

        if(!empty($excluded_label_ids)) 
            $inserted_data['excluded_label_ids'] = implode(',', $excluded_label_ids); 
        else 
            $inserted_data['excluded_label_ids'] = "";

        $fb_label_names = array();
        if(!empty($label_ids))
        {
            $fb_label_data=$this->basic->get_data("messenger_bot_broadcast_contact_group",array("where_in"=>array("id"=>$label_ids)));
            foreach ($fb_label_data as $key => $value) 
            {
               if($value['invisible']=='0')
                $fb_label_names[]=$value["group_name"];
            }  
        }

        $inserted_data['label_names'] = implode(',', $fb_label_names);

        if($this->basic->insert_data("email_sending_campaign",$inserted_data))
        {
            // getting inserted row id
            $campaign_id = $this->db->insert_id();

            $report_insert = array();
            foreach ($report as $key=>$value) 
            {
                $report_insert = array(
                    'user_id'              => $this->user_id,
                    'email_table_name'     => $value['email_table_name'],
                    'email_api_id'         => $value['email_api_id'],
                    'campaign_id'          => $campaign_id,
                    'contact_id'           => $value['contact_id'],
                    'subscriber_id'        => $value['subscriber_id'],
                    'contact_first_name'   => $value['contact_first_name'],
                    'contact_last_name'    => $value['contact_last_name'],
                    'contact_username'     => $value['contact_username'],
                    'contact_email'        => $key,
                    'contact_phone'        => $value['contact_phone_number'],
                    'sent_time'            => '',
                    'delivery_id'          => 'pending',
                    'processed'            => '0'
                );
                
                $this->basic->insert_data("email_sending_campaign_send", $report_insert);
            }

            echo json_encode(array("status"=>"1"));
        } else
        {
            echo json_encode(array('status'=>'0','message'=>$this->lang->line('Something went wrong, please try once again.')));
        }
    }

    public function edit_email_campaign($id=0)
    {
        if($this->session->userdata('user_type') != 'Admin' && !in_array(263,$this->module_access)) redirect('home/login_page', 'location');
        if($id==0) exit();

        $data['body'] = "sms_email_manager/email/email_campaign/edit_email_campaigns";
        $campaign_data = $this->basic->get_data("email_sending_campaign",array("where"=>array("id"=>$id,"user_id"=>$this->user_id)));
        $data['selected_contact_gorups'] = explode(",",$campaign_data[0]['contact_type_id']);
    
        $this->session->unset_userdata("attachment_file_path_name_scheduler");
        $this->session->unset_userdata("attachment_filename_scheduler");
        
        /**Get contact number and contact_type***/
        $user_id = $this->user_id;
        $table_type = 'sms_email_contact_group';   
        $where_type['where'] = array('user_id'=>$user_id);
        $info_type = $this->basic->get_data($table_type,$where_type,$select='', $join='', $limit='', $start='', $order_by='type');  
        $result = array();

        foreach ($info_type as  $value) 
        {
            $search_key = $value['id'];
            $search_type = $value['type'];

            $where_simple=array('sms_email_contacts.user_id'=>$this->user_id);
            $this->db->where("FIND_IN_SET('$search_key',sms_email_contacts.contact_type_id) !=", 0);
            $where=array('where'=>$where_simple);
            $this->db->select("count(sms_email_contacts.id) as number_count",false);    
            $contact_details=$this->basic->get_data('sms_email_contacts', $where, $select='', $join='', $limit='', $start='', $order_by='sms_email_contacts.first_name', $group_by='', $num_rows=0);
        
            foreach ($contact_details as $key2 => $value2) 
            {
                if($value2['number_count']>0)
                $group_name[$search_key] = $search_type." (".$value2['number_count'].")";
            }
                
        }                                                      

        /***get smtp  option***/
        if($this->config->item('email_api_access') == '1' && $this->session->userdata("user_type") == 'User')
        {                                                            
            /***get smtp  option***/
            $join = array('users'=>'email_smtp_config.user_id=users.id,left');
            $select = array('email_smtp_config.*','users.id AS usersID','users.user_type');
            $where_in = array('email_smtp_config.user_id'=>array('1',$this->user_id),'users.user_type'=>array('Admin','User'));
            $where = array('where_simple'=> array('status'=>'1'),'where_in'=>$where_in);
            $smtp_info=$this->basic->get_data('email_smtp_config', $where, $select, $join, $limit='', $start='', $order_by='email_address ASC', $group_by='', $num_rows=0);

            // echo "<pre>"; print_r($smtp_info); exit();
            
            $smtp_option=array();
            foreach ($smtp_info as $info) {
                $id="smtp_".$info['id'];
                $smtp_option[$id]="SMTP: ".$info['email_address'];
            }
            
            /***get mandrill option***/
            $join = array('users'=>'email_mandrill_config.user_id=users.id,left');
            $select = array('email_mandrill_config.*','users.id AS usersID','users.user_type');
            $where_in = array('email_mandrill_config.user_id'=>array('1',$this->user_id),'users.user_type'=>array('Admin','User'));
            $where = array('where_simple'=> array('status'=>'1'),'where_in'=>$where_in);
            $smtp_info=$this->basic->get_data('email_mandrill_config', $where, $select, $join, $limit='', $start='', $order_by='email_address ASC', $group_by='', $num_rows=0);
            
            foreach ($smtp_info as $info) {
                $id="mandrill_".$info['id'];
                $smtp_option[$id]="Mandrill: ".$info['email_address'];
            }

            /***get sendgrid option***/
            $join = array('users'=>'email_sendgrid_config.user_id=users.id,left');
            $select = array('email_sendgrid_config.*','users.id AS usersID','users.user_type');
            $where_in = array('email_sendgrid_config.user_id'=>array('1',$this->user_id),'users.user_type'=>array('Admin','User'));
            $where = array('where_simple'=> array('status'=>'1'),'where_in'=>$where_in);
            $smtp_info=$this->basic->get_data('email_sendgrid_config', $where, $select, $join, $limit='', $start='', $order_by='email_address ASC', $group_by='', $num_rows=0);
            
            foreach ($smtp_info as $info) {
                $id="sendgrid_".$info['id'];
                $smtp_option[$id]="SendGrid: ".$info['email_address'];
            }

            /***get mailgun option***/
            $join = array('users'=>'email_mailgun_config.user_id=users.id,left');
            $select = array('email_mailgun_config.*','users.id AS usersID','users.user_type');
            $where_in = array('email_mailgun_config.user_id'=>array('1',$this->user_id),'users.user_type'=>array('Admin','User'));
            $where = array('where_simple'=> array('status'=>'1'),'where_in'=>$where_in);
            $smtp_info=$this->basic->get_data('email_mailgun_config', $where, $select, $join, $limit='', $start='', $order_by='email_address ASC', $group_by='', $num_rows=0);
            
            foreach ($smtp_info as $info) {
                $id="mailgun_".$info['id'];
                $smtp_option[$id]="Mailgun: ".$info['email_address'];
            }

        } else
        {
            /***get smtp  option***/
            $where=array("where"=>array('user_id'=>$this->user_id,'status'=>'1'));
            $smtp_info=$this->basic->get_data('email_smtp_config', $where, $select='', $join='', $limit='', $start='', $order_by='email_address ASC', $group_by='', $num_rows=0);
            
            $smtp_option=array();
            foreach ($smtp_info as $info) {
                $id="smtp_".$info['id'];
                $smtp_option[$id]="SMTP: ".$info['email_address'];
            }
            
            /***get mandrill option***/
            $where=array("where"=>array('user_id'=>$this->user_id,'status'=>'1'));
            $smtp_info=$this->basic->get_data('email_mandrill_config', $where, $select='', $join='', $limit='', $start='', $order_by='email_address ASC', $group_by='', $num_rows=0);
            
            foreach ($smtp_info as $info) {
                $id="mandrill_".$info['id'];
                $smtp_option[$id]="Mandrill: ".$info['email_address'];
            }

            /***get sendgrid option***/
            $where=array("where"=>array('user_id'=>$this->user_id,'status'=>'1'));
            $smtp_info=$this->basic->get_data('email_sendgrid_config', $where, $select='', $join='', $limit='', $start='', $order_by='email_address ASC', $group_by='', $num_rows=0);
            
            foreach ($smtp_info as $info) {
                $id="sendgrid_".$info['id'];
                $smtp_option[$id]="SendGrid: ".$info['email_address'];
            }

            /***get mailgun option***/
            $where=array("where"=>array('user_id'=>$this->user_id,'status'=>'1'));
            $smtp_info=$this->basic->get_data('email_mailgun_config', $where, $select='', $join='', $limit='', $start='', $order_by='email_address ASC', $group_by='', $num_rows=0);
            
            foreach ($smtp_info as $info) {
                $id="mailgun_".$info['id'];
                $smtp_option[$id]="Mailgun: ".$info['email_address'];
            }
        }

        $api_arr = array();

        if($campaign_data[0]['configure_email_table'] == 'email_smtp_config')
        {
            $data['email_name'] = "smtp_".$campaign_data[0]['api_id'];
        }

        if($campaign_data[0]['configure_email_table'] == 'email_mandrill_config')
        {
            $data['email_name'] = "mandrill_".$campaign_data[0]['api_id'];
        }

        if($campaign_data[0]['configure_email_table'] == 'email_sendgrid_config')
        {
            $data['email_name'] = "sendgrid_".$campaign_data[0]['api_id'];
        }

        if($campaign_data[0]['configure_email_table'] == 'email_mailgun_config')
        {
            $data['email_name'] = "mailgun_".$campaign_data[0]['api_id'];
        }


        $data['email_option'] = $smtp_option;
        $data['campaign_data']=$campaign_data;
        $data['groups_name'] = $group_name;
        $data["time_zone"]   = $this->_time_zone_list();
        $data['page_title']  = $this->lang->line('Edit Email Campaign');
        $this->_viewcontroller($data);  
    }

    public function edit_email_campaign_action()
    {
        if($this->session->userdata('user_type') != 'Admin' && !in_array(263,$this->module_access)) exit;
        $this->ajax_check();

        $campaign_id          = $this->input->post("campaign_id");
        $campaign_name        = strip_tags(trim($this->input->post('campaign_name', true)));
        $email_subject        = strip_tags(trim($this->input->post('email_subject', true)));
        $email_message        = $this->input->post('message');
        $from_email           = strip_tags(trim($this->input->post('from_email', true)));
        $from_email_separate  = explode('_', $from_email);
        $api_id               = $from_email_separate[1];
        $configure_table_name = "email_smtp_config";
        $schedule_time        = $this->input->post('schedule_time');
        $time_zone            = strip_tags(trim($this->input->post('time_zone', true)));
        $attachement          = $this->session->userdata("attachment_file_path_name_scheduler");
        $filename             = $this->session->userdata("attachment_filename_scheduler");

        $existed_attachment   = $this->basic->get_data("email_sending_campaign", array('where'=>array('id'=>$campaign_id,'user_id'=>$this->user_id)),array('email_attachment'));

        // remove old attachment from upload/attachment directory
        if((isset($attachement) && $attachement != '') || (isset($filename) && $filename != ''))
        {
            if(isset($existed_attachment[0]['email_attachment']) && !empty($existed_attachment[0]['email_attachment'])) 
            {
                $file = FCPATH."upload/attachment/".$existed_attachment[0]['email_attachment'];
                if(file_exists($file))
                {
                    unlink($file);
                }
            } 
        }

        $this->session->unset_userdata("attachment_file_path_name_scheduler");
        $this->session->unset_userdata("attachment_filename_scheduler");


        if (strtolower($from_email_separate[0])=='mandrill') {
            $configure_table_name = "email_mandrill_config";
        } elseif (strtolower($from_email_separate[0])=='sendgrid') {
            $configure_table_name = "email_sendgrid_config";
        } elseif (strtolower($from_email_separate[0])=='mailgun') {
            $configure_table_name = "email_mailgun_config";
        }

        $successfully_sent = 0;
        $added_at          = date("Y-m-d H:i:s");
        $posting_status    = "0";

        $contacts_email_group = $this->input->post('contacts_id', true);

        if(!is_array($contacts_email_group))
            $contacts_email_group=array();  


        if(isset($contacts_email_group) && !empty($contacts_email_group))
            $contact_groupid    = implode(",",$contacts_email_group); 

        $contacts_id = array();
        $report = array();

        if(isset($contacts_email_group) && !empty($contacts_email_group))
        foreach ($contacts_email_group as $key => $value) 
        {
            $where_simple = array('sms_email_contacts.user_id'=>$this->user_id);
            $this->db->where("FIND_IN_SET('$value',sms_email_contacts.contact_type_id) !=", 0);
            $where = array('where'=>$where_simple);    
            $contact_details=$this->basic->get_data('sms_email_contacts', $where);       
            foreach ($contact_details as $key2 => $value2) 
            {
                $report[$value2['email']] = array(
                    'email_table_name'    => $configure_table_name,
                    'email_api_id'        => $api_id,
                    'contact_id'          => $value2['id'],
                    'contact_first_name'  => isset($value2['first_name']) ? $value2['first_name']:"",
                    'contact_last_name'   => isset($value2['last_name']) ? $value2['last_name']:"",
                    'contact_email'       => isset($value2['email']) ? $value2['email']:"",
                    'contact_phone_number'=> isset($value2['phone_number']) ? $value2['phone_number']:"",
                    'sent_time'           =>'pending',
                    'delivery_id'         =>'pending',
                );

                $contacts_id[] = $value2["id"];                
            }
        }


        $contacts_id = array_filter($contacts_id);
        $contacts_id = array_unique($contacts_id);
        $contacts_id = implode(',', $contacts_id);

        if($filename == "") 
        {
            $filename = $existed_attachment[0]['email_attachment'];
        }

        $thread = count($report);

        $updated_data = array(
            "user_id"               => $this->user_id,
            "configure_email_table" => $configure_table_name,
            "api_id"                => $api_id,
            "contact_ids"           => isset($contacts_id) ? $contacts_id:"",
            'contact_type_id'       => isset($contact_groupid) ? $contact_groupid:"",
            "campaign_name"         => $campaign_name,
            "email_subject"         => $email_subject,
            "email_message"         => str_replace(array("'",'"'),array('`','`'),$email_message),
            "email_attachment"      => $filename,
            "posting_status"        => $posting_status, 
            "schedule_time"         => $schedule_time,
            "time_zone"             => $time_zone,
            "total_thread"          => $thread,
            "successfully_sent"     => $successfully_sent,
            "created_at"            => $added_at,
            "report"                => json_encode($report),
        );

        if($this->basic->update_data("email_sending_campaign",array("id" => $campaign_id,"user_id"=>$this->user_id),$updated_data))
        {
            /* Delete the rows of updated campaign from sms_sending_campaign_send table */
            $this->basic->delete_data("email_sending_campaign_send", array("campaign_id" =>$campaign_id));

            $report_insert = array();
            foreach ($report as $key=>$value) 
            {
                $report_insert = array(
                    'user_id'              => $this->user_id,
                    'email_table_name'     => $value['email_table_name'],
                    'email_api_id'         => $value['email_api_id'],
                    'campaign_id'          => $campaign_id,
                    'contact_id'           => $value['contact_id'],
                    'contact_first_name'   => $value['contact_first_name'],
                    'contact_last_name'    => $value['contact_last_name'],
                    'contact_email'        => $key,
                    'contact_phone'        => $value['contact_phone_number'],
                    'sent_time'            => '',
                    'delivery_id'          => 'pending',
                    'processed'            => '0'
                );
                
                $this->basic->insert_data("email_sending_campaign_send", $report_insert);
            }

            echo json_encode(array("status"=>"1"));

        } else
        {
            echo json_encode(array('status'=>'0','message'=>$this->lang->line('Something went wrong, please try once again.')));
        }
    }

    public function email_logs()
    {
        if($this->session->userdata('user_type') != 'Admin' && !in_array(263,$this->module_access)) redirect('home/login_page', 'location');
        $data['body']="sms_email_manager/email/email_campaign/email_history";
        $data['page_title'] = $this->lang->line("Email History");
        $this->_viewcontroller($data);   
    }

    public function email_logs_data()
    {
        if($this->session->userdata('user_type') != 'Admin' && !in_array(263,$this->module_access)) exit;
        $this->ajax_check();

        $email_logs_date_range = $this->input->post("email_logs_date_range",true);

        $display_columns = array('#','id','send_as','to_email','details','sent_time','send_status');

        $page = isset($_POST['page']) ? intval($_POST['page']) : 1;
        $start = isset($_POST['start']) ? intval($_POST['start']) : 0;
        $limit = isset($_POST['length']) ? intval($_POST['length']) : 10;
        $sort_index = isset($_POST['order'][0]['column']) ? strval($_POST['order'][0]['column']) : 1;
        $sort = isset($display_columns[$sort_index]) ? $display_columns[$sort_index] : 'id';
        $order = isset($_POST['order'][0]['dir']) ? strval($_POST['order'][0]['dir']) : 'DESC';
        $order_by=$sort." ".$order;

        $where_simple=array();
        $where_simple['user_id'] = $this->user_id;

        if($email_logs_date_range!="")
        {
            $exp = explode('|', $email_logs_date_range);
            $from_date = isset($exp[0])?$exp[0]:"";
            $to_date   = isset($exp[1])?$exp[1]:"";

            if($from_date!="Invalid date" && $to_date!="Invalid date")
            {
                $from_date = date('Y-m-d', strtotime($from_date));
                $to_date   = date('Y-m-d', strtotime($to_date));
                $where_simple["Date_Format(sent_time,'%Y-%m-%d') >="] = $from_date;
                $where_simple["Date_Format(sent_time,'%Y-%m-%d') <="] = $to_date;
            }
        }

        $where  = array('where'=>$where_simple);

        $table = "email_history";
        $info = $this->basic->get_data($table,$where,$select='',$join='',$limit,$start,$order_by,$group_by='');

        $total_rows_array = $this->basic->count_row($table,$where,$count="id",$join,$group_by='');
        $total_result = $total_rows_array[0]['total_rows'];

        for ($i=0; $i <count($info); $i++) { 

            $email_api_infos = $this->basic->get_data($info[$i]['configure_table_name'],array('where'=>array('id'=>$info[$i]['api_id'])));

            if($info[$i]['configure_table_name'] == 'email_smtp_config')
                $info[$i]['send_as'] = "<div style='min-width:200px !important;'>"."SMTP - ".$email_api_infos[0]['email_address']."</div>";
            if($info[$i]['configure_table_name'] == 'email_mailgun_config')
                $info[$i]['send_as'] = "<div style='min-width:200px !important;'>"."Mailgun - ".$email_api_infos[0]['email_address']."</div>";
            if($info[$i]['configure_table_name'] == 'email_mandrill_config')
                $info[$i]['send_as'] = "<div style='min-width:200px !important;'>"."Mandrill - ".$email_api_infos[0]['email_address']."</div>";
            if($info[$i]['configure_table_name'] == 'email_sendgrid_config')
                $info[$i]['send_as'] = "<div style='min-width:200px !important;'>"."Sendgrid - ".$email_api_infos[0]['email_address']."</div>";

            $email_subject = $info[$i]['subject'];
            $email_message = $info[$i]['email_message'];
            $from_email    = $email_api_infos[0]['email_address'];
            $to_emails     = $info[$i]['to_email'];


            if($info[$i]['attachment'] !='')
                $email_attach  = $info[$i]['attachment'];
            else
                $email_attach = "";

            $info[$i]['details'] = "<a href='#' table_id='".$info[$i]['id']."' class='btn btn-circle btn-outline-primary see_email_message' title='".$this->lang->line("Click to See Details")."'><i class='fas fa-envelope'></i></a>
            <script>$('[data-toggle=\"tooltip\"]').tooltip();</script></script>";

            if($info[$i]['sent_time'] != "0000-00-00 00:00:00")
                $info[$i]['sent_time'] = "<div style='min-width:130px !important;' title='".$this->lang->line('Sent At')."'>".date("M j, y H:i",strtotime($info[$i]['sent_time']))."</div>";
            else 
                $info[$i]['sent_time'] = "<div style='min-width:100px !important;' class='text-muted'><i class='fas fa-exclamation-circle'></i> ".$this->lang->line('Not Sent')."</div>";

            if($info[$i]['send_status'] == 'success')
                $info[$i]['send_status'] = "<div class='text-success' style='min-width:140px !important;'><i class='fas fa-check-circle'></i> ".ucfirst($info[$i]['send_status'])."</div>";
            else if($info[$i]['send_status'] == "")
                $info[$i]['send_status'] = $info[$i]['send_status'];
            else
                $info[$i]['send_status'] = "<div class='text-danger text-justify'><i class='fas fa-exclamation-circle'></i> ".$info[$i]['send_status']."</div>";

            $info[$i]['to_email'] = "<div style='min-width:200px !important;'>".$info[$i]['to_email']."</div>";

        }

        $data['draw'] = (int)$_POST['draw'] + 1;
        $data['recordsTotal'] = $total_result;
        $data['recordsFiltered'] = $total_result;
        $data['data'] = convertDataTableResult($info, $display_columns ,$start,$primary_key="id");

        echo json_encode($data);  
    }

    public function see_email_details()
    {
        if($this->session->userdata('user_type') != 'Admin' && !in_array(263,$this->module_access)) exit;
        $this->ajax_check();

        $table_id = $this->input->post("table_id",true);
        if($table_id == '0' || $table_id == "") exit;

        $details = $this->basic->get_data("email_history",array("where"=>array("id"=>$table_id,"user_id"=>$this->user_id)));
        $from_email = $this->basic->get_data($details[0]['configure_table_name'],array("where"=>array("id"=>$details[0]['api_id'],"user_id"=>$this->user_id)));


        $email_subject = $details[0]['subject'];
        $email_message = $details[0]['email_message'];
        $email_subject = $details[0]['subject'];
        $toEmail       = $details[0]['to_email'];
        $attachment    = $details[0]['attachment'];
        $sentTime      = $details[0]['sent_time'];
        $fromEmail     = $from_email[0]['email_address'];

        $email_message = preg_replace('@(https?://([-\w\.]+)+(:\d+)?(/([\w/_\.\@]*(\?\S+)?)?)?)@', '<a href="$1">$1</a>', rawurldecode($email_message));

        $details_body = '
        <div class="card card-primary">
            <div class="row">
                <div class="col-12">
                    <div class="card-header d-block">
                        <h4 class="pointer" title="From: '.$fromEmail.' &#xA;To: '.$toEmail.'">'.$email_subject.'
                            <code class="float-right" title="'.$this->lang->line('Sent At').'">'.$sentTime.'</code>
                        </h4>
                    </div>
                    <div class="card-body">'.nl2br($email_message).'</div>
                </div>';
        if(isset($attachment) && $attachment !=''){
        $details_body .='
                <div class="col-12 col-md-4">
                    <div class="container">
                        <div class="wizard-steps">
                            <div class="wizard-step" title="'.$attachment.'">
                                <div class="wizard-step-icon">
                                    <i class="fas fa-paperclip"></i>
                                </div>
                                <div class="wizard-step-label">Attachment</div>
                            </div>
                        </div><br>
                        <div class="overlay">
                            <div class="text">
                                <a target="_BLANK" data-toggle="tooltip" title="'.$attachment.'" href="'.base_url('sms_email_manager/download_email_attachment/').$attachment."/".$this->user_id.'" class="btn btn-outline-primary"><i class="fas fa-cloud-download-alt"></i></a>
                            </div>
                        </div>
                    </div>
                </div>

                ';
        }

        $details_body .= '</div>
        </div>'."<script>$('[data-toggle=\"tooltip\"]').tooltip();</script></script>";

        echo $details_body;
    }

    public function _email_send_function($config_id_prefix="", $message_org="", $to_emails="", $subject="", $attachement='', $fileName='',$user_id='')
    {
        if($this->session->userdata('user_type') != 'Admin' && !in_array(263,$this->module_access)) redirect('home/login_page', 'location');

        $message_org = preg_replace('/data-cke-saved-src="(.+?)"/', '', $message_org);
        $message_org = preg_replace('/_moz_resizing="(.+?)"/', '', $message_org);
        
        $message = '<!DOCTYPE HTML>'.
        '<head>'.
        '<meta http-equiv="content-type" content="text/html">'.
        '<title>'.$subject.'</title>'.
        '</head>'.
        '<body>'.
        '<div id="outer" style="width: 90%;margin: 0 auto;margin-top: 10px;">'.$message_org.'</div>'.
        '</body>';


        if ($config_id_prefix=="" || $message=="" || $to_emails=="" || $subject=="") {
            return false;
        }

        if ($fileName=="0") {
            $fileName="";
            $attachement="";
        }

        if (!is_array($to_emails)) {
            $to_emails=array($to_emails);
        }
            
        $status="";
        
        /*****get the email configuration value*****/
        $from_email=$config_id_prefix;
        $from_email_separate=explode("_", $from_email);
        $config_type=$from_email_separate[0];
        $config_id=$from_email_separate[1];
        
        if ($config_type=='smtp') {
            $table_name="email_smtp_config";
        } elseif ($config_type=='mandrill') {
            $table_name="email_mandrill_config";
        } elseif ($config_type=='sendgrid') {
            $table_name="email_sendgrid_config";
        } elseif ($config_type=='mailgun') {
            $table_name="email_mailgun_config";
        } else {
            $table_name="";
        }
        
                    
        $where2=array("where"=>array('id'=>$config_id));
        $email_config_details=$this->basic->get_data($table_name, $where2, $select='', $join='', $limit='', $start='', $group_by='', $num_rows=0);

        $userid = $user_id;

        if (count($email_config_details)==0) {
            $status =  "Opps !!! Sorry no configuration is found";
            return $status;
        }

        if ($config_type=='smtp') 
        {
            foreach ($email_config_details as $send_info) 
            {
                $send_email = trim($send_info['email_address']);
                $smtp_host= trim($send_info['smtp_host']);
                $smtp_port= trim($send_info['smtp_port']);
                $smtp_user=trim($send_info['smtp_user']);
                $smtp_password= trim($send_info['smtp_password']);
                $smtp_type = trim($send_info['smtp_type']);
            }
            
            /*****Email Sending Code ******/
            $config = array(
              'protocol' => 'smtp',
              'smtp_host' => "{$smtp_host}",
              'smtp_port' => $smtp_port,
              'smtp_user' => "{$smtp_user}", // change it to yours
              'smtp_pass' => "{$smtp_password}", // change it to yours
              'mailtype' => 'html',
              'charset' => 'utf-8',
              'newline' =>"\r\n",
              'smtp_timeout'=>'30'
            );

            if($smtp_type != 'Default')
                $config['smtp_crypto'] = $smtp_type;

            $this->load->library('email', $config);
            $this->email->from($send_email); 
            
            if(is_array($to_emails) && count($to_emails)>1)
            {
                $no_reply_arr=explode("@",$send_email);
                if(isset($no_reply_arr[1]))
                $no_reply="do-not-reply@".$no_reply_arr[1];
                else $no_reply=$to_emails[0];
                $this->email->to($no_reply);
                $this->email->bcc($to_emails);
            }
            else $this->email->to($to_emails);

            $this->email->subject($subject);
            $this->email->message($message);
              
            if ($attachement) 
            {
                $this->email->attach($attachement);
            }

            try 
            {
                if($this->email->send())
                $response_smtp = "success";
                else $response_smtp = "error";                
            } 
            catch (Exception $e) 
            {
                $response_smtp = "error";
            }
              
            if($response_smtp!="error") 
            {
                $sent_time=date('Y-m-d H:i:s');
                foreach ($to_emails as $to_email) 
                {
                    $insert_data[]=
                        array
                        (
                            'user_id'=>$userid,
                            'configure_table_name'    =>$table_name,
                            'api_id'                =>$config_id,
                            'to_email'                =>$to_email,
                            'sent_time'             =>$sent_time,
                            'email_message'        =>$message,
                            'attachment'            =>$fileName,
                            'subject'               => $subject
                        );
                }
                    
                /***insert into database table email_history**/
                
                $this->db->insert_batch('email_history', $insert_data);
                $status = "Submited";
            } 
            else 
            {
                $status = "error in configuration";
            }
        }
        
        
        /***  End of Email sending by SMTP  ***/
        
        
        /***  If option is mandrill   ***/
        
        if ($config_type=='mandrill') 
        {
            foreach ($email_config_details as $send_info) 
            {
                $send_email= $send_info['email_address'];
                $api_id=$send_info['api_key'];
                $send_name=$send_info['your_name'];
            }
            $this->load->library('email_manager');
            $result = $this->email_manager->send_madrill_email($send_email, $send_name, $to_emails, $subject,$message, $api_id, $attachement, $fileName);
            
            if ($result!='error') 
            {
                $sent_time=date('Y-m-d H:i:s');
                foreach ($to_emails as $to_email) 
                {
                    $insert_data[]=
                        array
                        (
                            'user_id'=>$userid,
                            'configure_table_name'    =>$table_name,
                            'uid'                    =>$result[$to_email]['id'],
                            'api_id'                =>$config_id,
                            'to_email'                =>$to_email,
                            'send_status'            =>$result[$to_email]['status'],
                            'sent_time'             =>$sent_time,
                            'email_message'        =>$message,
                            'attachment'            =>$fileName,
                            'subject'               => $subject
                        );
                }

                /***insert into database table email_history**/
                
                $this->db->insert_batch('email_history', $insert_data);
                $status = "Submited";
            } 
            else 
            {
                $status ="error in configuration";
            }
        }
        
        
        
        /***** if gateway is sendgrid *****/
        if ($config_type=='sendgrid') 
        {
            $this->load->library('email_manager');
            foreach ($email_config_details as $send_info) 
            {
                $sendgrid_from_email= $send_info['email_address'];
                $this->email_manager->sendgrid_username=$send_info['username'];
                $this->email_manager->sendgrid_password=$send_info['password'];
            }
            
            $result = $this->email_manager->sendgrid_email_send($sendgrid_from_email, $to_emails, $subject, $message, $attachement, $fileName);
            
            if ($result['status']!='error') 
            {
                $sent_time=date('Y-m-d H:i:s');
                foreach ($to_emails as $to_email) 
                {
                    $insert_data[]=
                        array
                        (
                            'user_id'                =>$userid,
                            'configure_table_name'    =>$table_name,
                            'api_id'                =>$config_id,
                            'to_email'                =>$to_email,
                            'send_status'           =>$result['status'],
                            'sent_time'                =>$sent_time,
                            'email_message'        =>$message,
                            'attachment'            =>$fileName,
                            'subject'               => $subject
                        );
                }

                /***insert into database table email_history**/                
                $this->db->insert_batch('email_history', $insert_data);
                $status = "Submited";
            } 
            else 
            {
                $status ="error in configuration";
            }
        }
        
    
    
        if ($config_type=='mailgun') 
        {
            $this->load->library('email_manager');
            foreach ($email_config_details as $send_info) 
            {
                $send_email=$send_info['email_address'];
                $this->email_manager->mailgun_api_key=$send_info['api_key'];
                $this->email_manager->mailgun_domain=$send_info['domain_name'];
            }

            // echo "<pre>"; print_r($attachement); exit();
            
            $result = $this->email_manager->mailgun_email_send($send_email, $to_emails, $subject, $message, $attachement);
            
            if ($result['status']!='error') 
            {
                $sent_time=date('Y-m-d H:i:s');
                foreach ($to_emails as $to_email) 
                {
                    $insert_data[]=
                    array(
                            'user_id'                =>$userid,
                            'configure_table_name'   =>$table_name,
                            'api_id'                 =>$config_id,
                            'uid'                    =>$result['id'],
                            'to_email'               =>$to_email,
                            'send_status'            =>$result['status'],
                            'sent_time'              =>$sent_time,
                            'email_message'          =>$message,
                            'attachment'             =>$fileName,
                            'subject'                => $subject
                        );
                }

                /***insert into database table email_history**/
                
                $this->db->insert_batch('email_history', $insert_data);
                $status = "Submited";
            } 
            else 
            {
                $status ="error in configuration";
            }
        }
        
        return $status;
    }

    public function unsubscribe($contact_id,$email)
    {
        if($this->session->userdata('user_type') != 'Admin' && !in_array(263,$this->module_access)) exit;

        if($contact_id == '' || $email == '') exit;
        
        $data = array();
        $data['contact_id'] = isset($contact_id) ? $contact_id:"";
        $data['email_address'] = isset($email) ? urldecode($email):"";
        $info = $this->basic->get_data("sms_email_contacts", array('where'=>array("id"=>$contact_id, "email"=>urldecode($email))));

        if(isset($info) && !empty($info))
        {
            if(isset($info) && isset($info[0]['unsubscribed']) && $info[0]['unsubscribed'] =="0")
            {
                $data['status'] = "0";
            } else
            {
                $data['status'] = "1";
            }

            $this->load->view("sms_email_manager/email/email_campaign/unsubscribed_message",$data);

        } else {
            redirect('home/access_forbidden', 'location');
        }
    }

    public function unsubscribe_action()
    {
        if($this->session->userdata('user_type') != 'Admin' && !in_array(263,$this->module_access)) exit;

        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            redirect('home/access_forbidden', 'location');
        }

        $result = array();

        $contactid = trim($this->input->post("contactid",true));
        $email_address = trim($this->input->post("email",true));
        $btntype = trim($this->input->post("btntype",true));

        if(isset($btntype) && !empty($btntype) && $btntype == "unsub")
        {
            if($this->basic->update_data("sms_email_contacts", array("id"=>$contactid,"email"=>$email_address,"deleted"=>"0"), array("unsubscribed"=>"1")))
            {
                echo "1";
            } else
            {
                echo "0";
            }

        } else if(isset($btntype) && !empty($btntype) && $btntype == "sub")
        {
            if($this->basic->update_data("sms_email_contacts", array("id"=>$contactid,"email"=>$email_address,"deleted"=>"0"), array("unsubscribed"=>"0")))
            {
                echo "1";

            } else
            {
                echo "0";
            }

        }   
    }


    function _api_gateways()
    {
    	$gateway_lists = array(
			'plivo'               => 'Plivo [Required : Auth ID, Auth Token, Sender]',
			'twilio'              => 'Twilio [Required : Account Sid, Auth Token, From]',
			'nexmo'               => 'Nexmo [Required : API Key, API Secret, Sender]',
			'planet'              => 'Planet [Required : Username, Password, Sender.]',
			'semysms.net'         => 'semysms.net [Required : Auth Token, API ID [Use device ID in API ID]]',
			'clickatell-platform' => 'Clickatell-platform [Required : API ID]',
			'clickatell'          => 'Clickatell [Required : API Username, API Password, API ID]',
			'msg91.com'           => 'msg91.com [Required : Auth Key, Sender]',
			'africastalking.com'  => 'africastalking.com [Required : API Key, Sender ID/From [Use username in Sender ID/From]]',
                        // not tested
			// 'textlocal.in'        => 'textlocal.in [Required : API Key, Sender]',
			// 'sms4connect.com'     => 'sms4connect.com [Required : Account ID, Password, Mask]',
			// 'telnor.com'          => 'telnor.com [Required : MSISDN, Password, From]',
			// 'mvaayoo.com'         => 'mvaayoo.com [Required : Admin, Password, Sender ID]',
			// 'routesms.com'        => 'routesms.com [Required : Username, Password, Sender ID/From]',
			// 'trio-mobile.com'     => 'trio-mobile.com [Required : API Key, Sender ID]',
			// 'sms40.com'           => 'sms40.com [Required : Username, Password, Sender ID/From]',
			// 'infobip.com'         => 'infobip.com [Required : Username, Password, Sender ID/From]',
			// 'smsgatewayme'        => 'smsgatewayme [Required : API Token, API ID [Use device ID in API ID]]',
    	);

    	return $gateway_lists;
    }

}