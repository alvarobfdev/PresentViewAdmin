<?php
/**
 * Created by PhpStorm.
 * User: alvarobanofos
 * Date: 22/5/16
 * Time: 11:31
 */

namespace App\Http\Controllers;


use App\FirebaseCloudMessage;

class FirebaseMessagingController
{
    public static function sendMessage($data, $to=null) {

        $message = new FirebaseCloudMessage();
        if($to) {
            $message->to = $to;
        }
        $message->data = $data;


        // Create the context for the request
        $context = stream_context_create(array(
            'http' => array(
                // http://www.php.net/manual/en/context.http.php
                'method' => 'POST',
                'header' => "Authorization:key={$message->getAuthKey()}\r\n".
                    "Content-Type: application/json\r\n",
                'content' => $message->getPostData()
            )
        ));

    // Send the request
        $response = file_get_contents($message->getUrl(), FALSE, $context);

        // Check for errors
        if($response === FALSE){
            return ["fail_url_fcm" => "Fallo al enviar notificación a la app!"];
        }

    // Decode the response
        $responseData = json_decode($response, TRUE);

        dd($responseData);

    }
}