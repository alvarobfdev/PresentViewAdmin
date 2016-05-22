<?php
/**
 * Created by PhpStorm.
 * User: alvarobanofos
 * Date: 22/5/16
 * Time: 11:18
 */

namespace App;


use App\Http\Controllers\FirebaseMessagingController;
use Illuminate\Database\Eloquent\Model;

class Revision extends Model
{
    protected $table = "app_revisions";

    public static function updateRevision() {
        $revision = self::where("id", 1)->first();
        $revision->revision++;
        if(!$revision->save()) {
            return ["fail_bbdd"=>"Error grave 1003: Consulte a un administrador"];
        }

        $result = FirebaseMessagingController::sendMessage(
            [
                "subject" => "updatedRevision",
                "revision" => $revision->revision
            ]
        );

        if($result != "success") {
            $revision->revision--;
            $revision->save();
        }
        return $result;
    }
}