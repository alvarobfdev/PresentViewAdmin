<?php
/**
 * Created by PhpStorm.
 * User: alvarobanofos
 * Date: 22/2/16
 * Time: 18:05
 */

namespace App\Http;


use Illuminate\Database\Eloquent\Model;

class UsersAppModel extends Model
{
    protected $table = "app_users";

    public static function existsGoogleAccount($googleId) {
        return self::where("google_id", $googleId)->first();
    }

    public static function existsSimAccount($simId) {
        return self::where("sim_id", $simId)->first();
    }

    public function getShortUsername() {
        return mb_substr($this->name, 0, 3, 'UTF-8').mb_substr($this->surname, -3, null, 'UTF-8');
    }

}