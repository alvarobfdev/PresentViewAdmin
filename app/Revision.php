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
        $revision->save();

        FirebaseMessagingController::sendMessage(
            [
                "subject" => "updatedRevision",
                "revision" => $revision->revision
            ]
        );
    }
}