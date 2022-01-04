<?php

namespace App\Lib;
use \Mycrypt;
class General {

    public static function error_res($msg = "", $args = array()) {
        $msg = $msg == "" ? "error" : $msg;
        $msg_id = 'error.' . $msg;
        $converted = \Lang::get($msg_id, $args);
        $msg = $msg_id == $converted ? $msg : $converted;
        $json = array('flag' => 0, 'msg' => $msg);
        return $json;
    }

    public static function success_res($msg = "", $args = array()) {
        $msg = $msg == "" ? "success" : $msg;
//        $msg = \Lang::get('response.'.$msg_id, array('name' => 'Paresh Bhai'));
        $msg_id = 'success.' . $msg;
        $converted = \Lang::get($msg_id, $args);
        $msg = $msg_id == $converted ? $msg : $converted;
        $json = array('flag' => 1, 'msg' => $msg);
//        global $_EXECUTION;
//        $json['request_load'] = $_EXECUTION;
        return $json;
//        return Response::json($json);
//        Respo
    }

    public static function validation_error_res($msg = "", $args = array()) {
        $msg = $msg == "" ? "validation error" : $msg;
        $msg_id = 'error.' . $msg;
        $converted = \Lang::get($msg_id, $args);
        $msg = $msg_id == $converted ? $msg : $converted;
        $json = array('flag' => 2, 'msg' => $msg);
        return $json;
    }

    public static function info_res($msg = "", $args = array()) {
        $msg = $msg == "" ? "information" : $msg;
        $msg_id = 'info.' . $msg;
        $converted = \Lang::get($msg_id, $args);
        $msg = $msg_id == $converted ? $msg : $converted;
        $json = array('flag' => 3, 'msg' => $msg);
        return $json;
    }

    public static function email_verify_error_res($msg = "", $args = array()) {
        $msg = $msg == "" ? "Email Id is Not Verified" : $msg;
        $msg_id = 'error.' . $msg;
        $converted = \Lang::get($msg_id, $args);
        $msg = $msg_id == $converted ? $msg : $converted;
        $json = array('flag' => 4, 'msg' => $msg);
        return $json;
    }
    
    public static function mobile_verify_error_res($msg = "", $args = array()) {
        $msg = $msg == "" ? "mobile_not_verified" : $msg;
        $msg_id = 'error.' . $msg;
        $converted = \Lang::get($msg_id, $args);
        $msg = $msg_id == $converted ? $msg : $converted;
        $json = array('flag' => 4, 'msg' => $msg);
        return $json;
    }
    
    public static function maintenance_mode_error_res($msg = "", $args = array()) {
        $msg = $msg == "" ? "maintenance_mode_on" : $msg;
        $msg_id = 'error.' . $msg;
        $converted = \Lang::get($msg_id, $args);
        $msg = $msg_id == $converted ? $msg : $converted;
        $json = array('flag' => 5, 'msg' => $msg);
        return $json;
    }

    public static function request_token_expire_res($msg = "", $args = array()) {
        $msg = $msg == "" ? "Request token invalid" : $msg;
        $msg_id = 'error.' . $msg;
        $converted = \Lang::get($msg_id, $args);
        $msg = $msg_id == $converted ? $msg : $converted;
        $json = array('flag' => 7, 'msg' => $msg);
        return $json;
    }

    public static function session_expire_res($msg = "", $args = array()) {
        $msg = $msg == "" ? "Session Expired" : $msg;
        $msg_id = 'error.' . $msg;
        $converted = \Lang::get($msg_id, $args);
        $msg = $msg_id == $converted ? $msg : $converted;
        $json = array('flag' => 8, 'msg' => $msg);
        return $json;
    }

    public static function _url($str) {
        if (is_string($str))
            return preg_match("/[a-z0-9]+([\-\.]{1}[a-z0-9]+)*\.[a-z]{2,5}(:[0-9]{1,5})?(\/.*)?$/", $str) ? TRUE : FALSE;
        return FALSE;
    }

    public static function dd($data, $exit = 0) {
        if (in_array(\App::environment(), array("production")))
            return;
        if (is_array($data) || is_object($data)) {
            echo "<pre>";
            print_r($data);
            echo "</pre>";
        } else {
            echo $data . "<br>";
        }
        if ($exit == 1)
            exit;
    }

    public static function rand_str($len) {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ_-.';
        $randomString = '';
        for ($i = 0; $i < $len; $i++) {
            $randomString .= $characters[rand(0, strlen($characters) - 1)];
        }
        return $randomString;
    }

    public static function MongoDate($date_str = "") {
        $date_str = $date_str == "" ? date("Y-m-d H:i:s") : $date_str;
        $time = strtotime($date_str) * 1000;
        $date = date("Y-m-d H:i:s",  $time);
        if(class_exists('\MongoDate'))
        {
            $date = new \MongoDate($time);
        }
        else if(class_exists('\MongoDB\BSON\UTCDateTime'))
        {
            $date = new \MongoDB\BSON\UTCDateTime($time);
        }
        return $date;
    }

    public static function get_notification_str($type = "") {
        $nofication = \Config::get("static.notification_type");
        $str = $type;
        if (isset($nofication[$type])) {
            $str = $nofication[$type]['str'];
        }
        return $str;
    }

    public static function is_json($string) {
        json_decode($string);
        return (json_last_error() == JSON_ERROR_NONE);
    }

    public static function del_dir($dir) {
        $files = array_diff(scandir($dir), array('.', '..'));
        foreach ($files as $file) {
            (is_dir("$dir/$file")) ? self::del_dir("$dir/$file") : unlink("$dir/$file");
        }
        return rmdir($dir);
    }

    public static function long_id($id, $id_prefix = "") {
        
        if (\Config::get("constant.ENC_ID") == 1)
            return \Mycrypt::encrypt($id_prefix . $id);
        return $id;
    }

    public static function short_id($id) {
        if (\Config::get("constant.ENC_ID") == 1)
            return explode("_", \Mycrypt::decrypt($id))[1];
        return $id;
    }

    public static function group_by($array, $key) {
        $return = array();
        foreach ($array as $val) {
            $return[$val[$key]][] = $val;
        }
        return $return;
    }
    
    public static function get_external_ip()
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "http://ipinfo.io");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        $result = curl_exec($ch);
        curl_close($ch);
        if(\General::is_json($result))
        {
            $arr = json_decode($result,true);
            return $arr['ip'];
        }
        return "";
    }
    
    public static function xml_to_array($buffer)
    {
        
        $parser = xml_parser_create(''); 
        xml_parser_set_option($parser, XML_OPTION_TARGET_ENCODING, "UTF-8");
        xml_parser_set_option($parser, XML_OPTION_CASE_FOLDING, 0); 
        xml_parser_set_option($parser, XML_OPTION_SKIP_WHITE, 1); 
        xml_parse_into_struct($parser, trim($buffer), $xml_values); 
        xml_parser_free($parser); 
        $xml_array = array(); 
        $parents = array(); 
        $opened_tags = array(); 
        $arr = array(); 
        $current = &$xml_array;
        //Go through the tags. 
        $repeated_tag_index = array();//Multiple tags with same name will be turned into an array 
        foreach($xml_values as $data) { 
        unset($attributes,$value);//Remove existing values, or there will be trouble 

        //This command will extract these variables into the foreach scope 
        // tag(string), type(string), level(int), attributes(array). 
        extract($data);//We could use the array by itself, but this cooler. 

        $result = array(); 
        $attributes_data = array(); 
         
        if(isset($value)) { 
            $result['value'] = $value; //Put the value in a assoc array if we are in the 'Attribute' mode 
        } 

         
        //See tag status and do the needed. 
        if($type == "open") {//The starting of the tag '<tag>' 
            $parent[$level-1] = &$current; 
            if(!is_array($current) or (!in_array($tag, array_keys($current)))) { //Insert New tag 
                $current[$tag] = $result; 
                if($attributes_data) $current[$tag. '_attr'] = $attributes_data; 
                $repeated_tag_index[$tag.'_'.$level] = 1; 

                $current = &$current[$tag]; 

            } else { //There was another element with the same tag name 

                if(isset($current[$tag][0])) {//If there is a 0th element it is already an array 
                    $current[$tag][$repeated_tag_index[$tag.'_'.$level]] = $result; 
                    $repeated_tag_index[$tag.'_'.$level]++; 
                } else {//This section will make the value an array if multiple tags with the same name appear together 
                    $current[$tag] = array($current[$tag],$result);//This will combine the existing item and the new item together to make an array
                    $repeated_tag_index[$tag.'_'.$level] = 2; 
                     
                    if(isset($current[$tag.'_attr'])) { //The attribute of the last(0th) tag must be moved as well 
                        $current[$tag]['0_attr'] = $current[$tag.'_attr']; 
                        unset($current[$tag.'_attr']); 
                    } 

                } 
                $last_item_index = $repeated_tag_index[$tag.'_'.$level]-1; 
                $current = &$current[$tag][$last_item_index]; 
            } 

        } elseif($type == "complete") { //Tags that ends in 1 line '<tag />' 
            //See if the key is already taken. 
            if(!isset($current[$tag])) { //New Key 
                $current[$tag] = $result; 
                $repeated_tag_index[$tag.'_'.$level] = 1; 
                $current[$tag. '_attr'] = $attributes_data; 

            } else { //If taken, put all things inside a list(array) 
                if(isset($current[$tag][0]) and is_array($current[$tag])) {//If it is already an array... 

                    // ...push the new element into that array. 
                    $current[$tag][$repeated_tag_index[$tag.'_'.$level]] = $result; 
                     
                    if($priority == 'tag' and $get_attributes and $attributes_data) { 
                        $current[$tag][$repeated_tag_index[$tag.'_'.$level] . '_attr'] = $attributes_data; 
                    } 
                    $repeated_tag_index[$tag.'_'.$level]++; 

                } else { //If it is not an array... 
                    $current[$tag] = array($current[$tag],$result); //...Make it an array using using the existing value and the new value 
                    $repeated_tag_index[$tag.'_'.$level] = 1; 
                    if($priority == 'tag' and $get_attributes) { 
                        if(isset($current[$tag.'_attr'])) { //The attribute of the last(0th) tag must be moved as well 
                             
                            $current[$tag]['0_attr'] = $current[$tag.'_attr']; 
                            unset($current[$tag.'_attr']); 
                        } 
                         
                        if($attributes_data) { 
                            $current[$tag][$repeated_tag_index[$tag.'_'.$level] . '_attr'] = $attributes_data; 
                        } 
                    } 
                    $repeated_tag_index[$tag.'_'.$level]++; //0 and 1 index is already taken 
                } 
            } 

        } elseif($type == 'close') { //End of tag '</tag>' 
            $current = &$parent[$level-1]; 
        } 
    }
    return $xml_array;
    }
    
    public static function search_and_splice($search_key,$search_value,$index_array)
    {
        foreach ($index_array as $key => $val)
        {
            if($val[$search_key] == $search_value)
            {
                array_splice($index_array, $key,1);
                break;
            }
        }
        return $index_array;
    }
    
    public static function array_indexof_assoc_search($search_array,$index_array)
    {
        \General::dd($search_array);
        \General::dd($index_array);
        if (empty($search_array) || empty($index_array)) { 
            return -1; 
        }
        foreach ($index_array as $key => $val)
        {
            $exists = true; 
            foreach ($search_array as $skey => $svalue) { 
                $exists = ($exists && IsSet($index_array[$key][$skey]) && $index_array[$key][$skey] == $svalue); 
            } 
            if($exists){ return $key; } 
        }
        return -1;
    }

    public static function remove_device_token($device_token){
        $users = \App\Models\User::where('device_tokens.token', $device_token)->get();
        foreach ($users as $user){
            $tokens = $user->device_tokens;
            for( $i=count($tokens)-1 ; $i>=0 ; $i--){
                if($tokens[$i]['token'] == $device_token){
                    array_splice($tokens, $i, 1);
                }
            }

            $user->device_tokens = $tokens;
            $user->save();
        }
    }
    
    public static function date_dif($start, $end, $return_type) {
        $dEnd = strtotime($end);
        $dStart = strtotime($start);
        $dDiff = ($dEnd - $dStart);
        if (strtolower($return_type) == "y") {
            $dDiff = $dDiff / 60 / 60 / 24 / 365;
        } else if (strtolower($return_type) == "m") {
            $dDiff = ($dEnd - $dStart) / 60 / 60 / 24 / 30;
        } else if (strtolower($return_type) == "d") {
            $dDiff = ($dEnd - $dStart) / 60 / 60 / 24;
        } else if (strtolower($return_type) == "h") {
            $dDiff = $dDiff / 60 / 60;
        } else if (strtolower($return_type) == "i") {
            $dDiff = ($dEnd - $dStart) / 60;
        } else if (strtolower($return_type) == "s") {
            $dDiff = $dEnd - $dStart;
        }
        return floor($dDiff);
    }
    
    public static function time_elapsed_string($from_date,$to_date, $full = false) {
        
        $f = strtotime($from_date);
        $t = strtotime($to_date);
        
        if($t < $f){
            $to_date = date('Y-m-d H:i:s',strtotime($to_date.' +1 day'));
        }
        
        $now = new \DateTime($to_date);
        $ago = new \DateTime($from_date);
        $diff = $now->diff($ago);

        
        
        $diff->w = floor($diff->d / 7);
        $diff->d -= $diff->w * 7;

        $string = array(
            'y' => 'year',
            'm' => 'month',
            'w' => 'week',
            'd' => 'day',
            'h' => 'jam',
            'i' => 'menit',
            's' => 'second',
        );
        foreach ($string as $k => &$v) {
            if ($diff->$k) {
//                $v = $diff->$k . ' ' . $v . ($diff->$k > 1 ? 's' : '');
                $v = $diff->$k . ' ' . $v;
            } else {
                unset($string[$k]);
            }
        }

        if($string)
            $str = implode(', ', $string);
        
        if (!$full)
            $string = array_slice($string, 0, 1);
        return $string ? $str : 'just now'; 
    }
    
    public static function number_format($number,$dec=3){
        return number_format($number/1000,$dec);
    }
    
    
    public static function commonData($param) {
         
        if($param=='name'){
            
           $user = \Auth::guard('admin')->user()->toArray();
            return  $user['username'];
        }
        return ;
    }
    
    public static function get_field_array($list=array(),$field){
        $tmpArray=array();
        
        foreach($list as $l)
        {
               array_push($tmpArray,$l[$field]);
        }
      
        return $tmpArray;
    }
   
    
    public static  function count_age($dob){
        
        $curYear=date('Y');
        return $curYear-$dob;
        
    }
    
     public static function status_class_msg($status,$statusList){
        $tclass['cls']='';
        $tclass['txt']='';
        $tmpClass=[];
        foreach($statusList as $key=>$value){
            if($key==$status){
                $tmpClass=  explode('|', $value);
            }
        }
        if(count($tmpClass)<2){
            $tmpClass=  explode('|',$statusList['else']);
        }
        $tclass['cls']=$tmpClass[0];
        $tclass['txt']=$tmpClass[1];
        return $tclass;
    }
    
}
