<?php
/**
 * Created by PhpStorm.
 * User: alvarobanofos
 * Date: 6/2/16
 * Time: 21:40
 */

namespace App;


use Illuminate\Database\Eloquent\Model;

class AnswersModel extends Model
{
    protected $table = "app_possible_answers";

    public function getPercentage() {
        $totalAnswers = UserAnswerModel::where("question_id", $this->question_id)->count();
        $totalThisAnswer = UserAnswerModel::where("question_id", $this->question_id)
            ->where("answer_id", $this->id)->count();

        return number_format((float)(($totalThisAnswer/$totalAnswers)*100), 2, '.', '');

    }

}