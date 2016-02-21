<?php
/**
 * Created by PhpStorm.
 * User: alvarobanofos
 * Date: 6/2/16
 * Time: 21:37
 */

namespace App;


use Illuminate\Database\Eloquent\Model;

class QuestionsModel extends Model
{
    protected $table = "app_questions";

    public function answers() {
        return $this->hasMany('App\AnswersModel', 'question_id');
    }
}