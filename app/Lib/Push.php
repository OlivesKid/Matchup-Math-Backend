<?php 
namespace App\Lib;

class Push {

    public static $certificate_path = "";
    public static $passphrase = "12345";
    public static $apns_host = "";
    public static $android_app_key = "AAAAv3VKnSM:APA91bFhtpAN-FhEtd5WnJN_25Vc2kqT8JTJdt4C8479ZLiZnvTvABJx_2dHh45qWX4T565AFkLyFFyVeDKHUh8UBQs5SHz17e4bnp0IodDR7n-9V2l9yEG3gwfXuMvGA1KjZLxCHGf5";
    //public static $android_push_base_url = 'https://android.googleapis.com/gcm/send';
    public static $android_push_base_url = 'https://android.googleapis.com/gcm/send';

    public function __construct()
    {
        
    }

    public static function android_push($device_token, $msg = array(), $title = array(), $badge = array(), $screen = array(), $meta = array(), $sound = array(), $image = array())
    {
        if (!isset($msg[0]) || !isset($badge[0])){
            return \General::error_res("Push data not properly configured");
        }
        
        for ($i = 0; $i < count($device_token); $i++)
        {
            try {
                $info_str = array(
                    'message' => isset($msg[$i]) ? $msg[$i] : (isset($msg[0]) ? $msg[0] : ""),
                    'title' => isset($title[$i]) ? $title[$i] : (isset($title[0]) ? $title[0] : ""),
                    'screen' => isset($screen[$i]) ? $screen[$i] : (isset($screen[0]) ? $screen[0] : ""),
                    'bedge' => isset($badge[$i]) ? $badge[$i] : (isset($badge[0]) ? $badge[0] : 1),
                    'meta' => isset($meta[$i]) ? $meta[$i] : (isset($meta[0]) ? $meta[0] : ""),
                    "sound" => isset($sound[$i]) && $sound[$i] != "" ? $sound[$i] : "push_sound.wav",
                    'image' => isset($image[$i]) ? $image[$i] : "",
                );
                
                $info_str = json_encode($info_str);
                
                $fields = array(
                    'registration_ids' => array($device_token[$i]),
                    'data' => array(
                        'message' => $info_str,
                    ),
                );

                $headers = array(
                    'Authorization: key=' . self::$android_app_key,
                    'Content-Type: application/json'
                );
                
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, self::$android_push_base_url);
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
                curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
                $result = curl_exec($ch);
                
                if(\General::is_json($result))
                {
                    $arr = json_decode($result,true);
                    if(!isset($arr['success']) || $arr['success'] == 0)
                    {
                        \Log::error("Android Push Error: ".$result);
                        if($arr['results'][0]['error'] == "InvalidRegistration" || $arr['results'][0]['error'] == "NotRegistered"){
                            \Log::info($device_token);
//                            \General::remove_device_token($device_token[$i]);
                        }
                        return \General::error_res("Push Error: ".$result);
                    }
                }                
            } catch (Exception $ex) {
                \Log::error($ex->getMessage());
                return \General::error_res($ex->getMessage());
            }
            curl_close($ch);
        }
        
        \Log::info("Android notification sent successfully.");
        return \General::success_res("android_push_sent");
    }

    public static function iphone_push($device_token, $msg = array(), $title = array(), $badge = array(), $screen = array(), $meta = array(), $sound = array(), $image = array())
    {   
        \Log::info(\Request::fullUrl());
        // if (env("IPHONE_DEVELOPMENT") == "sandbox")
        // {
        //     self::$apns_host = 'ssl://gateway.sandbox.push.apple.com:2195';
        //     self::$certificate_path = realpath(__DIR__) . '/developmentCertificates.pem';
        //     self::$passphrase = "matchupAPNS@bitrix2021#Dev";
        // }
        // else
        // {
        //     self::$apns_host = 'ssl://gateway.push.apple.com:2195';
        //     self::$certificate_path = realpath(__DIR__) . '/DistributionCertificates.pem';
        //     self::$passphrase = "bitrix";
        // }
        \Log::info($device_token);
        self::$apns_host = 'ssl://api.push.apple.com:443';
        self::$certificate_path = realpath(__DIR__) . '/DistributionCertificates.pem';
        self::$passphrase = "matchupAPNS@bitrix2021#Dist";
        // self::$passphrase = "1234";


        if (!isset($msg[0]) || !isset($badge[0]))
            return \General::error_res("invalid_push_data");
        try {
            $result = false;
            $streamContext = stream_context_create();
            stream_context_set_option($streamContext, 'ssl', 'local_cert', self::$certificate_path);
            stream_context_set_option($streamContext, 'ssl', 'passphrase', self::$passphrase);
            $fp = stream_socket_client(self::$apns_host, $error, $errorString, 60, STREAM_CLIENT_CONNECT, $streamContext);
            // $fp = fsockopen(self::$apns_host, 443, $errno, $errstr, 30);            
            
            for ($i = 0; $i < count($device_token); $i++)
            {
                if ($device_token[$i] == "")
                    continue;
                
                $push_arr = array(
                    'alert' => isset($title[$i]) ? $title[$i] : (isset($title[0]) ? $title[0] : ""),
                    "msg" => isset($msg[$i]) ? $msg[0] : $msg[$i],
                    "sound" => isset($sound[$i]) && $sound[$i] != "" ? $sound[$i] : "push_sound.wav",
                    'bedge' => isset($badge[$i]) ? $badge[$i] : (isset($badge[0]) ? $badge[0] : ""),
                    'screen' => isset($screen[$i]) ? $screen[$i] : (isset($screen[0]) ? $screen[0] : ""),
                    'meta' => isset($meta[$i]) ? $meta[$i] : (isset($meta[0]) ? $meta[0] : ""),
                );
                \Log::info($push_arr);
            
                $payload = array();
                $payload['aps'] = $push_arr;
                $payload = json_encode($payload);
                
                $apnsMessage = chr(0) . chr(0) . chr(32) . pack('H*', str_replace(' ', '', $device_token[$i])) . chr(0) . chr(strlen($payload)) . $payload;
                // $apnsMessage = 'Local Message';
                // if (env('APP_ENV','local') == 'local') {
                    $apnsMessage = chr(0) . pack('n', 32) . pack('H*', $device_token[$i]) . pack('n', strlen($payload)) . $payload;
                // }
                \Log::info("........fp...........");
                \Log::info($fp);
                // \Log::info('apns message :'.$apnsMessage);   
                $result = fwrite($fp, $apnsMessage,strlen($apnsMessage));
                //@fwrite($fp, $apnsMessage);
                \Log::info('...........Push Result............');
                \Log::info($result);
            }

            
            fclose($fp);
            //@fclose($fp);

            if(!$result){
                return \General::error_res("iphone_push_sent_fail");
            }
            
        } catch (Exception $e) {
            \Log::error("\r\niphone push: " . $e->getMessage());
        }
        \Log::info("Iphone notification sent successfully.");
        return \General::success_res("iphone_push_sent");
    }

    public static function clear_payload($str)
    {
        $arr = explode(" ", $str);
        $r = "";
        foreach ($arr as $v)
        {

            if (preg_match("/\\\\u[a-z0-9A-Z]{4,}+/", $v))
            {
                $v = str_replace("\u", "u", $v);
            }

            $r.=$v . " ";
        }
        return $r;
    }
    
}

?>