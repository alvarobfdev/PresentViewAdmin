<?php
/**
 * Created by PhpStorm.
 * User: alvarobanofos
 * Date: 6/2/16
 * Time: 21:37
 */

namespace App;


use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class QuestionsModel extends Model
{
    protected $table = "app_questions";

    public function answers() {
        return $this->hasMany('App\AnswersModel', 'question_id');
    }

    public function userAnswers() {
        return $this->hasMany('App\UserAnswerModel', 'question_id');
    }

    public function winnerUser() {
        return $this->belongsTo('App\Http\UsersAppModel', 'winner_user_id');

    }

    public function isFinished() {
        $time_ini = Carbon::createFromFormat("Y-m-d H:i:s", $this->time_ini, 'Europe/Madrid')->timestamp;
        $time_end = $time_ini + $this->duration;
        return time() > $time_end;
    }
}