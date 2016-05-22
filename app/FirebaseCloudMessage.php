<?php
/**
 * Created by PhpStorm.
 * User: alvarobanofos
 * Date: 22/5/16
 * Time: 11:33
 */

namespace App;


class FirebaseCloudMessage
{
    protected $url = "https://fcm.googleapis.com/fcm/send";
    protected $authKey = "AIzaSyBPKEUeFO-0tDTq8hhOHn3BCp2NoYWAoNQ";

    public $to = "/topics/global";
    public $data = [];

    /**
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @return string
     */
    public function getAuthKey()
    {
        return $this->authKey;
    }


    public function getPostData() {
        $postData = [
            "data" => $this->data,
            "to" => $this->to
        ];
        return json_encode($postData);
    }
}