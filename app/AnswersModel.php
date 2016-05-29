<?php
/**
 * Created by PhpStorm.
 * User: alvarobanofos
 * Date: 6/2/16
 * Time: 21:40
 */

namespace App;


use App\Http\Controllers\QuestionsController;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

class AnswersModel extends Model
{
    protected $table = "app_possible_answers";


    public static $ages = ["16-20", "21-30", "31-40", "41-50", "51-60", "61-70", "71-80"];

    /**
     * AnswersModel constructor.
     */



    public function getPercentage($answers_count) {
        $totalAnswers = $answers_count;
        $totalThisAnswer = UserAnswerModel::where("question_id", $this->question_id)
            ->where("answer_id", $this->id)->count();

        if($totalAnswers > 0)
            return number_format((float)(($totalThisAnswer/$totalAnswers)*100), 2, '.', '');
        else return 0.00;

    }

    public function setPercentageProvinces(&$data, $provinces_json, $answers) {

        $time_ini = microtime(true);
        foreach($provinces_json as $province) {

            $data[] = $this->getPercentageProvince($answers, $this->id, $province->Id);
        }


    }


    public function setPercentageAges(&$data, $answers) {

        foreach(self::$ages as $age) {
            $age = explode("-", $age);
            $ageMin = $age[0];
            $ageMax = $age[1];
            $data[] = $this->getPercentageAge($answers, $this->id, $ageMin, $ageMax);
        }

    }



    private function getPercentageProvince($answers, $answerId, $provinceId) {
       $totalAnswers = 0;


        $time_end = microtime(true);
        echo "POINT TIME 3.2: ".($time_end-QuestionsController::$time_ini) . "<br>";
       foreach($answers as $answer) {
           if($answer->user->provincia == $provinceId) {
               $totalAnswers++;
           }
       }
        $totalThisAnswer = 0;
        foreach($answers as $answer) {
            if($answer->user->provincia == $provinceId && $answer->answer_id == $answerId) {
                $totalThisAnswer++;
            }
        }

        if($totalAnswers > 0)
            return number_format((float)(($totalThisAnswer/$totalAnswers)*100), 2, '.', '');
        else return 0.00;

    }

    private function getPercentageAge($answers, $answerId, $ageMin, $ageMax) {
        $totalAnswers = 0;
        $now = Carbon::now();
        $dateMax = Carbon::create(($now->year)-$ageMin, $now->month, $now->day);
        $dateMin = Carbon::create(($now->year)-$ageMax, $now->month, $now->day);


        foreach($answers as $answer) {

            $birthdate = Carbon::createFromFormat("Y-m-d", $answer->user->birthdate);

            if($birthdate->timestamp >= $dateMin->timestamp && $birthdate->timestamp <= $dateMax->timestamp) {
                $totalAnswers++;
            }
        }
        $totalThisAnswer = 0;
        foreach($answers as $answer) {
            $birthdate = Carbon::createFromFormat("Y-m-d", $answer->user->birthdate);
            if($birthdate->timestamp >= $dateMin->timestamp && $birthdate->timestamp <= $dateMax->timestamp && $answer->answer_id == $answerId) {
                $totalThisAnswer++;
            }
        }

        if($totalAnswers > 0)
            return number_format((float)(($totalThisAnswer/$totalAnswers)*100), 2, '.', '');
        else return 0.00;

    }

}