<?php
/**
 * Created by PhpStorm.
 * User: alvarobanofos
 * Date: 6/2/16
 * Time: 21:40
 */

namespace App;


use Illuminate\Database\Eloquent\Model;

class UserAnswerModel extends Model
{
    protected $table = "app_answers";

    public function user() {
        return $this->belongsTo("App\Http\UsersAppModel","user_id");
    }
}