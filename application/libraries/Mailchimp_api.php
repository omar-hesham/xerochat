<?php 

class Mailchimp_api 
{
	function syncMailchimp($data='', $apikey, $listId,$tags='') 
 	{
       
        $apikey_explode = explode('-',$apikey); // The API ID is the last part of your api key, after the hyphen (-), 
        if(is_array($apikey_explode) && isset($apikey_explode[1])) $api_id=$apikey_explode[1];
        else $api_id="";

        if($apikey=="" || $api_id=="" || $listId=="" || $data==""){

              $result['error']="Error in API ID Settings";
              return json_encode($result);
        }
      
        $auth = base64_encode( 'user:'.$apikey );
		
        $insert_data=array
        (
			'email_address'  => $data['email'],
			'status'         => 'subscribed', // "subscribed","unsubscribed","cleaned","pending"
			'merge_fields'  => array('FNAME'=>$data['firstname'],'LNAME'=>$data['lastname'],'CITY'=>'','MMERGE5'=>"Subscriber")	
	    );

        if($tags!=""){
            
            if(is_array($tags))
                $insert_data['tags']=$tags; 
            else
                $insert_data['tags']=array($tags); 
        }
            
			
		$insert_data=json_encode($insert_data);
 	
		$url="https://".$api_id.".api.mailchimp.com/3.0/lists/".$listId."/members/";

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json','Authorization: Basic '.$auth));
        curl_setopt($ch, CURLOPT_USERAGENT, 'PHP-MCAPI/2.0');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 50);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $insert_data);
        return $result = curl_exec($ch);
    }


    function get_all_list($apikey) 
 	{
       
        $apikey_explode = explode('-',$apikey); // The API ID is the last part of your api key, after the hyphen (-), 
        if(is_array($apikey_explode) && isset($apikey_explode[1])) $api_id=$apikey_explode[1];
        else $api_id="";

        if($apikey=="" || $api_id=="") {
            $result['error']=true;
            return json_encode($result);
        }
      
        $auth = base64_encode( 'user:'.$apikey );

		$url="https://".$api_id.".api.mailchimp.com/3.0/lists?fields=lists";

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json','Authorization: Basic '.$auth));
        curl_setopt($ch, CURLOPT_USERAGENT, 'PHP-MCAPI/2.0');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 50);
       
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
     
        $result = curl_exec($ch);
        
        $curl_info=curl_getinfo($ch);
        if($curl_info['http_code']!='200'){
            $result=array();
            $result['error']=true;
            return json_encode($result);    
        }

        return $result;  
    }
}