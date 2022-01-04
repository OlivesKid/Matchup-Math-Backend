<?php
namespace App\Lib;

class FCM {

  function __construct() {

  }

  public static function send_notification($registatoin_ids, $notification,$device_type, $notification_data = []) {

      $url = 'https://fcm.googleapis.com/fcm/send';
      $server_key  =  \App\Models\Admin\Settings::where('name','firebase_server_key')->first()['val'];
      $fields = array (
          'registration_ids' => array (
                  $registatoin_ids
          ),
          'notification' => array (
              "title" => "DRYP Wallet",
              "body" => $notification,
              'sound'=>'default'
          )
      );
      $headers = array('Authorization:key='.$server_key,'Content-Type:application/json');
      $ch = curl_init();
      curl_setopt($ch, CURLOPT_URL, $url);
      curl_setopt($ch, CURLOPT_POST, true);
      curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
      curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);     // Disabling SSL Certificate support temporarly
      curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
      $result = curl_exec($ch);
      $result = json_decode($result,true);
     
      if ($result == false) {
        $res = \General::error_res('Something went wrong.');
      }elseif ($result['success']  == 1 && $result['failure'] == 0) {
        $res = \General::success_res('Notification send successfully.');
      }else{
        $res = \General::error_res('Can not send Notification as user is logged out.');
      }
      curl_close($ch);
      return $res;
  }
  public static function send_notification_with_data($registatoin_ids, $notification,$device_type, $notification_data = []) {
    \Log::info("======================== Send Notification API Called ====================");
    \Log::info("registration_ids...........");
    \Log::info($registatoin_ids);
    $url = 'https://fcm.googleapis.com/fcm/send';
    $server_key  =  \App\Models\Setting::where('name','firebase_server_key')->first()['val'];
    // $notification['click_action'] = "OPEN_ACTIVITY_1";
    if($device_type == config('constant.ANDROID_APP_DEVICE') || $device_type == config('constant.IPHONE_APP_DEVICE')){
      $fields = array(
          'registration_ids' => $registatoin_ids,
          'content_available'=> true,
          'notification' => array (
              "title" => $notification_data['0'],
              "body" => $notification['0'],
              'sound'=>'default'
          ),
      );
      \Log::info(json_encode($fields));
    }
    

    else{
      return \General::error_res(' Invalid Device type.');
    }
    $headers = array('Authorization:key='.$server_key,'Content-Type:application/json');

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);     // Disabling SSL Certificate support temporarly
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
    $result = curl_exec($ch);
    
    $result = json_decode($result,true);
    \Log::info("..........Result...........");
    \Log::info($result);
    if ($result == false) {
      $res = \General::error_res('Something went wrong.');
    }elseif ($result['success']  == 1 && $result['failure'] == 0) {
      $res = \General::success_res('Notification send successfully.');
    }else{
      $res = \General::error_res('Can not send Notification as user is logged out.');
    }
    curl_close($ch);
    return $res;
}
}