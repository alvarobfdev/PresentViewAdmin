<?php
/**
 * Created by PhpStorm.
 * User: alvarobanofos
 * Date: 22/5/16
 * Time: 11:18
 */

namespace App;


use Illuminate\Database\Eloquent\Model;

class Revision extends Model
{
    protected $table = "app_revisions";

    public static function updateRevision() {
        $revision = self::where("id", 1)->first();
        $revision->revision++;
        $revision->save();

        //Fire send message event
    }
}