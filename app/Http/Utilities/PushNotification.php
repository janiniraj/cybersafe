<?php

namespace App\Http\Utilities;

/**
 * Created By : Niraj Jani
 * Created at : 02/05/2016
 * Push Notification Class
 */
class PushNotification {
	/**
	 * Created By : Niraj Jani
     * Created at : 02/05/2016
	 * PushNotification
	 * @param  array $msg
	 * @param  string $type
	 * @param  array $devicetoken
	 * @return bool
	 */
    public function _pushNotification($msg, $type, $deviceToken, $otherFields = null)
    {
        if ($deviceToken)
        {
            switch ($type)
            {
                case 'ios':
                    //return $this->_pushToIos($deviceToken, $msg);
                    return true;
                    break;

                case 'android':
                    return $this->_pushToAndroid($deviceToken, $msg);
                    break;

                default:
                    echo 'Invalid Type Passed';
            }
            return false;
        } else {
            return false;
        }
    }

    /**
     * Created By : Niraj Jani
     * Created at : 02/05/2016
	 * PushNotification for android
	 * @param  array $registrationIds
	 * @param  array $msg
	 * @return bool
	 */
    public function _pushToAndroid($registrationIds, $msg, $otherFields = null)
    {
        //$googleToken = env('ANDROID_KEY');
        $googleToken = 'AAAAM_8x7To:APA91bEf4YVgAS0-9-ipTFAk71vFFmsZ9XB_XFqSTqcwXlofrfFj-MaRTIFT7vZ8qRgQu5_XOAb6TqDDI0NqdwrHUzyaRPoNliegU7LYMNd50X0_P1xUsq8rGlzkh4_VfJtwHE_IQNh8';

        $msgArray = array
        (
            'body' 	=> $msg,
            'title'	=> $msg,
            'icon'	=> 'ic_notification',/*Default Icon*/
            //'sound' => 'mySound'/*Default sound*/
        );
        $fields = array
        (
            'to'		    => $registrationIds,
            'notification'	=> $msgArray
        );

        if($otherFields)
        {
            array_merge($fields, $otherFields);
        }

        $headers = array
        (
            'Authorization: key=' . $googleToken,
            'Content-Type: application/json'
        );

        #Send Reponse To FireBase Server
        $ch = curl_init();
        curl_setopt( $ch,CURLOPT_URL, 'https://fcm.googleapis.com/fcm/send' );
        curl_setopt( $ch,CURLOPT_POST, true );
        curl_setopt( $ch,CURLOPT_HTTPHEADER, $headers );
        curl_setopt( $ch,CURLOPT_RETURNTRANSFER, true );
        curl_setopt( $ch,CURLOPT_SSL_VERIFYPEER, false );
        curl_setopt( $ch,CURLOPT_POSTFIELDS, json_encode( $fields ) );
        $result = curl_exec($ch );

        if (curl_errno($ch)) {
            // this would be your first hint that something went wrong
            die('Couldn\'t send request: ' . curl_error($ch));
        } else {
            // check the HTTP status code of the request
            $resultStatus = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            if ($resultStatus != 200) {
                die('Request failed: HTTP status code: ' . $resultStatus);
            }
        }
        curl_close($ch);
        dd($headers);
        return true;
    }

    /**
     * Created By : Niraj Jani
     * Created at : 02/05/2016
	 * PushNotification for IOS
	 * @param  array $devicetoken
	 * @param  array $msg
	 * @return bool
	 */
    public function _pushtoios($devicetoken, $message) {

        $passphrase = 'apple';
        $ctx = stream_context_create();
        //stream_context_set_option($ctx, 'ssl', 'local_cert', TMP . 'apns-dev.pem');
        stream_context_set_option($ctx, 'ssl', 'local_cert', $this->baseDir() . 'apns-prod.pem');
        stream_context_set_option($ctx, 'ssl', 'passphrase', $passphrase);
        //$fp = stream_socket_client('ssl://gateway.sandbox.push.apple.com:2195', $err, $errstr, 60, STREAM_CLIENT_CONNECT | STREAM_CLIENT_PERSISTENT, $ctx);
        $fp = stream_socket_client('ssl://gateway.push.apple.com:2195', $err, $errstr, 60, STREAM_CLIENT_CONNECT | STREAM_CLIENT_PERSISTENT, $ctx);
        if (!$fp)
            exit("Failed to connect amarnew: $err $errstr" . PHP_EOL);

        $body['aps'] = array(
            'badge' => +1,
            'alert' => $message,
            'sound' => 'default'
        );
        $payload = json_encode($body);
        $msg = chr(0) . pack('n', 32) . pack('H*', $devicetoken) . pack('n', strlen($payload)) . $payload;

        $result = fwrite($fp, $msg, strlen($msg));
        if (!$result) {
            return false;
        } else {
            return true;
        }
        fclose($fp);
    }

}
