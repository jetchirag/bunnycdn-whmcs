<?php

/* Original work at 
* https://github.com/codewithmark/bunnycdn/
* @codewithmark
*/

class BunnyCDN
{    
    private $api_key_account;
	private $api_key_storage;
    
    function __construct( $api_key_account ) {
		if(!$api_key_account){			 
            throw new Exception('missing api key account');
			die();
		}
		$this->api_key_account = $api_key_account;
        return $this;
	}
	
	protected $api_url = array
	(
		"zone" => "https://bunnycdn.com/api",
		'storage' => 'https://storage.bunnycdn.com'
	);

	public function GetZoneList()
	{	
		/*
			will get all of the zones for the account
		*/
		
		if( !$this->api_key_account)
		{
			return array('status' =>'error' ,'code' =>'api_key_account' ,'msg'=> 'missing acount api key');
			die();
		}

		$key =  $this->api_key_account;
		$api_url = $this->api_url['zone'].'/pullzone';

		$get_header = $this->create_header($key);

		$api_call = $this->run( array('call_method' => 'GET', 'api_url' => $api_url,'header' => $get_header , ) );
		
		if($api_call['http_code'] !=200)
		{
			//error message
			$request_array =  json_decode(json_encode($api_call['data']));
			$result = array
			(	
				"status" => 'error',
				"http_code"=>$api_call['http_code'],
				"msg" => json_decode($request_array)->Message , 
			);
			return $result;
			die();
		}

		$zone_data =  json_decode($api_call['data']);	

 		$a1 = array();

 		foreach ($zone_data as  $k1 => $v1) 
		{			  
			$arr_hostnames  = array();

			//--->get all the hostnames > start
			if($v1->Hostnames)
			{
				foreach ($v1->Hostnames as $key => $v2) 
				{
					array_push($arr_hostnames,  $v2->Value);
				}
			}
			//--->get all the hostnames > end

			$d = array
			(	
				"zone_id" => $v1->Id,
				"zone_name"=>$v1->Name,
				"monthly_bandwidth_used" =>$this->format_bytes($v1->MonthlyBandwidthUsed),				
				"host_names" =>$arr_hostnames,
			);
			array_push($a1,$d);
		}
		
		return array('status' => 'success', 'zone_smry'=>$a1,"zone_details" => $zone_data);

	}
	public function GetZone($zone_id = '')
	{	
		/*
			will get a user zone for the account
		*/
		if( !$this->api_key_account)
		{
			return array('status' =>'error' ,'code' =>'api_key_account' ,'msg'=> 'missing acount api key');
			die();
		}
		
		if(!$zone_id)
		{
			return array('status' =>'error' ,'code' =>'zone_id' ,'msg'=> 'missing zone id');
			die();
		}


		$key =  $this->api_key_account;
		$api_url = $this->api_url['zone'].'/pullzone/'.$zone_id;

		$get_header = $this->create_header($key);
		$post_data_array = array('id'=>$zone_id);

		$api_call = $this->run( array('call_method' => 'GET', 'api_url' => $api_url,'header' => $get_header , 'post_data_array'=>$post_data_array) );
		

		if($api_call['http_code'] !=200)
		{
			//error message
			$request_array =  json_decode(json_encode($api_call['data']));
			$result = array
			(	
				"status" => 'error',
				"http_code"=>$api_call['http_code'],
				"msg" => json_decode($request_array) , 
			);
			return $result;
			die();
		}

		$zone_data =  json_decode($api_call['data']);

		$a1 = array();
		$arr_hostnames  = array();

		//--->get all the hostnames > start
		if($zone_data->Hostnames)
		{
			foreach ($zone_data->Hostnames as $key => $v1) 
			{
				array_push($arr_hostnames,  $v1->Value);
			}
		}
		//--->get all the hostnames > end

		$d = array
		(	
			"zone_id" => $zone_data->Id,
			"zone_name"=>$zone_data->Name,
			"monthly_bandwidth_used" =>$this->format_bytes($zone_data->MonthlyBandwidthUsed),				
			"host_names" =>$arr_hostnames,
		);
		array_push($a1,$d);

		return array('status' => 'success', 'zone_smry'=>$a1,"zone_details" => $zone_data);
		die(); 
	}


	public function CreateNewZone($zone_name = '', $zone_url = '')
	{	
		/*
			will create a new zone for the account
		*/	

		if( !$this->api_key_account)
		{
			return array('status' =>'error' ,'code' =>'api_key_account' ,'msg'=> 'missing acount api key');
			die();
		}

		if(!$zone_name)
		{
			return array('status' =>'error' ,'code' =>'zone_name' ,'msg'=> 'missing zone name');
			die();
		}

		if(!$zone_url)
		{
			return array('status' =>'error' ,'code' =>'zone_url' ,'msg'=> 'missing zone url');
			die();
		}

		$key =  $this->api_key_account;
		$api_url = $this->api_url['zone'].'/pullzone';

		$get_header = $this->create_header($key);


		$post_data_array = array('Name' => $zone_name, 'OriginUrl' => $zone_url);

		$api_call = $this->run( array('call_method' => 'POST', 'api_url' => $api_url,'header' => $get_header , 'post_data_array'=>$post_data_array) );
		 
		if($api_call['http_code'] !=201)
		{
			//error message
			$request_array =  json_decode(json_encode($api_call['data']));
			$result = array
			(	
				"status" => 'error',
				"http_code"=>$api_call['http_code'],
				"msg" => json_decode($request_array) , 
			);
			return $result;
			die();
		}

		//convert to php array for data parsing
		$zone_data =  json_decode($api_call['data']);

		//--->get all the hostnames > start
		$cdnurl = '';
		if($zone_data->Hostnames)
		{
			foreach ($zone_data->Hostnames as $key => $v1) 
			{
				$cdnurl = $v1->Value;				 
			}
		}
		//--->get all the hostnames > end

	 

		return array
		(
			'status' => 'success', 
			"zone_id" => $zone_data->Id,
			"zone_name"=>$zone_data->Name,
			"origin_url"=>$zone_data->OriginUrl,
			"cdn_url"=>$cdnurl,			
			"zone_details" => $zone_data
		);
		die();
	}


	public function DeleteZone($zone_id= '')
	{	
		/*
			will delete a zone for the account
		*/	

		if( !$this->api_key_account)
		{
			return array('status' =>'error' ,'code' =>'api_key_account' ,'msg'=> 'missing acount api key');
			die();
		}

		if(!$zone_id)
		{
			return array('status' =>'error' ,'code' =>'zone_id' ,'msg'=> 'missing zone id');
			die();
		}
 

		$key =  $this->api_key_account;
		$api_url = $this->api_url['zone'].'/pullzone/'. $zone_id;

		$get_header = $this->create_header($key);
 
		$api_call = $this->run( array('call_method' => 'DELETE', 'api_url' => $api_url,'header' => $get_header , ) );
		
		
		if($api_call['http_code'] !=200 && $api_call['http_code'] !=302)
		{
			//error message
			$request_array =  json_decode(json_encode($api_call['data']));
			$result = array
			(	
				"status" => 'error',
				"http_code"=>$api_call['http_code'],
				"msg" => json_decode($request_array) , 
			);
			return $result;
			die();
		}		 
 		
 		return array(
			'status' => 'success', 
			"msg" => $api_call,
 
		);
 		//return $api_call;
		die();
	}
    
    // Custom function added by @jetchirag
    
    public function UpdateZone($zone_id='', $updateFields){
        if( !$this->api_key_account)
		{
			return array('status' =>'error' ,'code' =>'api_key_account' ,'msg'=> 'missing acount api key');
			die();
		}

		if(!$zone_id)
		{
			return array('status' =>'error' ,'code' =>'zone_id' ,'msg'=> 'missing zone id');
			die();
		}
        
        $zoneDetails = $this->GetZone($zone_id);
        
        if ($zoneDetails['status'] != 'success') {
            return array('status' =>'error' ,'msg'=> 'Unable to get data for zone');
			die(); 
        }
        
        $zoneDetails = $zoneDetails['zone_details'];
        
        $post_data_array = array_merge((array)$zoneDetails, (array)$updateFields);
        
		$key =  $this->api_key_account;
		$api_url = $this->api_url['zone'].'/pullzone/'. $zone_id;

		$get_header = $this->create_header($key);

		$api_call = $this->run( array('call_method' => 'POST', 'api_url' => $api_url,'header' => $get_header , 'post_data_array'=>$post_data_array) );
		
		
		if($api_call['http_code'] !=200 && $api_call['http_code'] !=302)
		{
			//error message
			$request_array =  json_decode(json_encode($api_call['data']));
			$result = array
			(	
				"status" => 'error',
				"http_code"=>$api_call['http_code'],
				"msg" => json_decode($request_array) , 
			);
			return $result;
			die();
		}		 
 		
 		return array(
			'status' => 'success', 
			"msg" => $api_call,
 
		);
 		//return $api_call;
		die();
    }

	public function PurgeZoneCache($zone_id= '')
	{	
		/*
			will purge cache for the whole zone
		*/	

		if( !$this->api_key_account)
		{
			return array('status' =>'error' ,'code' =>'api_key_account' ,'msg'=> 'missing acount api key');
			die();
		}

		if(!$zone_id)
		{
			return array('status' =>'error' ,'code' =>'zone_id' ,'msg'=> 'missing zone id');
			die();
		}
 

		$key =  $this->api_key_account;
		$api_url = $this->api_url['zone'].'/pullzone/'. $zone_id.'/purgeCache';

		$get_header = $this->create_header($key);


		$api_call = $this->run( array('call_method' => 'POST', 'api_url' => $api_url,'header' => $get_header , ) );
		
		
		if($api_call['http_code'] !=200  )
		{
			//error message
			$request_array =  json_decode(json_encode($api_call['data']));
			$result = array
			(	
				"status" => 'error',
				"http_code"=>$api_call['http_code'],
				"msg" => json_decode($request_array) , 
			);
			return $result;
			die();
		}
		
 		return array(
			'status' => 'success', 
			"msg" => $api_call, 
		);
		die();
	}


	public function AddHostName($zone_id = '', $host_name_url = '')
	{	
		/*
			will add a host name for the zone
		*/	

		if( !$this->api_key_account)
		{
			return array('status' =>'error' ,'code' =>'api_key_account' ,'msg'=> 'missing acount api key');
			die();
		}

		if(!$zone_id)
		{
			return array('status' =>'error' ,'code' =>'zone_id' ,'msg'=> 'missing zone id');
			die();
		}

		if(!$host_name_url)
		{
			return array('status' =>'error' ,'code' =>'host_name_url' ,'msg'=> 'missing host name url');
			die();
		}

		$key =  $this->api_key_account;
		$api_url = $this->api_url['zone'].'/pullzone/addHostname';

		$get_header = $this->create_header($key);


		$post_data_array = array('PullZoneId' => $zone_id, 'Hostname' => $host_name_url);

		$api_call = $this->run( array('call_method' => 'POST', 'api_url' => $api_url,'header' => $get_header , 'post_data_array'=>$post_data_array) );
		 
		if($api_call['http_code'] !=200  )
		{
			//error message
			$request_array =  json_decode(json_encode($api_call['data']));
			$result = array
			(	
				"status" => 'error',
				"http_code"=>$api_call['http_code'],
				"msg" => json_decode($request_array) , 
			);
			return $result;
			die();
		}
		
 		return array(
			'status' => 'success', 
			"msg" => $api_call, 
		);
		die();
	}


	public function DeleteHostName($zone_id = '', $host_name_url = '')
	{	
		/*
			will delete a host name for the zone
		*/	

		if( !$this->api_key_account)
		{
			return array('status' =>'error' ,'code' =>'api_key_account' ,'msg'=> 'missing acount api key');
			die();
		}

		if(!$zone_id)
		{
			return array('status' =>'error' ,'code' =>'zone_id' ,'msg'=> 'missing zone id');
			die();
		}

		if(!$host_name_url)
		{
			return array('status' =>'error' ,'code' =>'host_name_url' ,'msg'=> 'missing host name url');
			die();
		}

		$key =  $this->api_key_account;
		$api_url = $this->api_url['zone'].'/pullzone/deleteHostname?id='.$zone_id.'&hostname='.$host_name_url ;

		$get_header = $this->create_header($key);


		$api_call = $this->run( array('call_method' => 'DELETE', 'api_url' => $api_url,'header' => $get_header , ) );
		 
		if($api_call['http_code'] !=200  )
		{
			//error message
			$request_array =  json_decode(json_encode($api_call['data']));
			$result = array
			(	
				"status" => 'error',
				"http_code"=>$api_call['http_code'],
				"msg" => json_decode($request_array) , 
			);
			return $result;
			die();
		}
		
 		return array(
			'status' => 'success', 
			"msg" => $api_call, 
		);
		die();
	}

	public function AddBlockedIP($zone_id = '', $blocked_ip = '')
	{	
		/*
			will add a blocked ip for the zone
		*/	

		if( !$this->api_key_account)
		{
			return array('status' =>'error' ,'code' =>'api_key_account' ,'msg'=> 'missing acount api key');
			die();
		}

		if(!$zone_id)
		{
			return array('status' =>'error' ,'code' =>'zone_id' ,'msg'=> 'missing zone id');
			die();
		}

		if(!$blocked_ip)
		{
			return array('status' =>'error' ,'code' =>'blocked_ip' ,'msg'=> 'missing blocked ip');
			die();
		}

		$key =  $this->api_key_account;
		$api_url = $this->api_url['zone'].'/pullzone/addBlockedIp' ;

		$get_header = $this->create_header($key);


		$post_data_array = array('PullZoneId' => $zone_id, 'BlockedIp' => $blocked_ip);

		$api_call = $this->run( array('call_method' => 'POST', 'api_url' => $api_url,'header' => $get_header , 'post_data_array'=>$post_data_array) );
		 
		if($api_call['http_code'] !=200  )
		{
			//error message
			$request_array =  json_decode(json_encode($api_call['data']));
			$result = array
			(	
				"status" => 'error',
				"http_code"=>$api_call['http_code'],
				"msg" => json_decode($request_array) , 
			);
			return $result;
			die();
		}
		
 		return array(
			'status' => 'success', 
			"msg" => $api_call, 
		);
		die();
	}


	public function RemoveBlockedIP($zone_id = '', $blocked_ip = '')
	{	
		/*
			will remove a blocked ip for the zone
		*/	

		if( !$this->api_key_account)
		{
			return array('status' =>'error' ,'code' =>'api_key_account' ,'msg'=> 'missing acount api key');
			die();
		}

		if(!$zone_id)
		{
			return array('status' =>'error' ,'code' =>'zone_id' ,'msg'=> 'missing zone id');
			die();
		}

		if(!$blocked_ip)
		{
			return array('status' =>'error' ,'code' =>'blocked_ip' ,'msg'=> 'missing blocked ip');
			die();
		}

		$key =  $this->api_key_account;
		$api_url = $this->api_url['zone'].'/pullzone/removeBlockedIp' ;

		$get_header = $this->create_header($key);


		$post_data_array = array('PullZoneId' => $zone_id, 'BlockedIp' => $blocked_ip);

		$api_call = $this->run( array('call_method' => 'POST', 'api_url' => $api_url,'header' => $get_header , 'post_data_array'=>$post_data_array) );
		 
		if($api_call['http_code'] !=200  )
		{
			//error message
			$request_array =  json_decode(json_encode($api_call['data']));
			$result = array
			(	
				"status" => 'error',
				"http_code"=>$api_call['http_code'],
				"msg" => json_decode($request_array) , 
			);
			return $result;
			die();
		}
		
 		return array(
			'status' => 'success', 
			"msg" => $api_call, 
		);
		die();
	}

	public function PurgeURL($url = '')
	{	
		/*
			will purge a url for the account
		*/	

		if( !$this->api_key_account)
		{
			return array('status' =>'error' ,'code' =>'api_key_account' ,'msg'=> 'missing acount api key');
			die();
		}

		if(!$url)
		{
			return array('status' =>'error' ,'code' =>'url' ,'msg'=> 'missing url');
			die();
		}
 
		$key =  $this->api_key_account;
		$api_url = $this->api_url['zone'].'/purge?url='.$url ;

		$get_header = $this->create_header($key);


		//$post_data_array = array('PullZoneId' => $zone_id, 'BlockedIp' => $blocked_ip);

		$api_call = $this->run( array('call_method' => 'POST', 'api_url' => $api_url,'header' => $get_header , ));
		 
		if($api_call['http_code'] !=200  )
		{
			//error message
			$request_array =  json_decode(json_encode($api_call['data']));
			$result = array
			(	
				"status" => 'error',
				"http_code"=>$api_call['http_code'],
				"msg" => json_decode($request_array) , 
			);
			return $result;
			die();
		}
		
 		return array(
			'status' => 'success', 
			"msg" => $api_call, 
		);
		die();
	}

	//--->process functions > start

	private function create_header($api_key)
	{
		$header = array('Content-Type:application/json','accesskey:'.$api_key.'' );
		return $header;
	}

	private function run($call_arr = array('call_method' => 'GET', 'api_url' => 'api_url','header' => array(),'post_data_array' => array() , ) )
	{ 
		$call_method 		= isset($call_arr['call_method']) ? $call_arr['call_method'] : 'GET' ;
	    $api_url 			= isset($call_arr['api_url']) ? $call_arr['api_url'] : 'api_url' ;
	    $header 			= isset($call_arr['header']) ? $call_arr['header'] : '' ;
	    $post_data_array 	= isset($call_arr['post_data_array']) ? $call_arr['post_data_array'] : '' ;


	    $post_data = json_encode($post_data_array);

	    $curl = curl_init($api_url);   

	   	curl_setopt($curl, CURLOPT_HTTPHEADER,$header);
	    
	    curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $call_method); 

	    curl_setopt($curl, CURLOPT_URL, $api_url);
	    
	    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
	    
	    curl_setopt($curl, CURLOPT_POST, 1);
	    curl_setopt($curl, CURLOPT_POSTFIELDS, $post_data);
	    
	    $result = curl_exec($curl);
	    $http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);

	    curl_close($curl);	 

	    
	    //For error checking
	    if ( $result === false )
	    {
	        return array('status' =>'error' ,'code'=> 'curl_error', 'result' => curl_error($curl) ,);
	    	die();
	    }
	 	
	 	return array('http_code'=> $http_code, 'data' =>  $result,);
	}	  
	//--->process functions > end
	
	//--->private functions > start

	public function format_bytes($bytes, $force_unit = NULL, $format = NULL, $si = TRUE)
	{
	    // Format string
	    $format = ($format === NULL) ? '%01.2f %s' : (string) $format;

	    // IEC prefixes (binary)
	    if ($si == FALSE OR strpos($force_unit, 'i') !== FALSE)
	    {
	        $units = array('B', 'KiB', 'MiB', 'GiB', 'TiB', 'PiB');
	        $mod   = 1024;
	    }
	    // SI prefixes (decimal)
	    else
	    {
	        $units = array('B', 'kB', 'MB', 'GB', 'TB', 'PB');
	        $mod   = 1000;
	    }
	    // Determine unit to use
	    if (($power = array_search((string) $force_unit, $units)) === FALSE)
	    {
	        $power = ($bytes > 0) ? floor(log($bytes, $mod)) : 0;
	    }
	    return sprintf($format, $bytes / pow($mod, $power), $units[$power]);
	}
    
    private  function fix_url($url ='')
	{
		return str_replace("\\", "/",  $url );
	}

	private function seo_file_name($file_name)
	{ 	
		/*
			will convert file name into seo url file name
			
			i.e.
			$file_name = 'code with mark !@#$%^*()_+~ $$%& _03e05 122-9****.mp4';

			//output will be
			code-with-mark-03e05-122-9.mp4

			Note only use this for file names and not for folder names!!!

		*/	
		
		$path_info 		= pathinfo($file_name);		
		$info_dir_name  = preg_replace("/[\s]/", "-", strtolower($path_info['dirname']) ); 
		

		$info_file_name = $path_info['filename'];
		$info_file_ext 	= $path_info['extension'];		

		$string = $info_file_name ;

	    $src = 'àáâãäçèéêëìíîïñòóôõöøùúûüýÿßÀÁÂÃÄÇÈÉÊËÌÍÎÏÑÒÓÔÕÖØÙÚÛÜÝ';
	    $rep = 'aaaaaceeeeiiiinoooooouuuuyysAAAAACEEEEIIIINOOOOOOUUUUY';
	    // strip off accents (assuming utf8 PHP - note strtr() requires single-byte)
	    $string = strtr(utf8_decode($string), utf8_decode($src), $rep);
	    // convert to lower case
	    $string = strtolower($string);
	    // strip all but alphanumeric, whitespace, dot, underscore, hyphen
	    $string = preg_replace("/[^a-z0-9\s._-]/", "", $string);
	    // merge multiple consecutive whitespaces, dots, underscores, hyphens
	    $string = preg_replace("/[\s._-]+/", " ", $string);
	    // convert whitespaces to hyphens
	    $string = preg_replace("/[\s]/", "-", $string);
		
		
		if(substr($info_dir_name,1))
		{
			$file_path 	= $info_dir_name."/".$string.'.'.$info_file_ext;
		}
		else
		{
			$file_path 	= "/". $string.'.'.$info_file_ext;
		}
 
	    return $file_path;
	}

	
	//--->private functions > end
}	
