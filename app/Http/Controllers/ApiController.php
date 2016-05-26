<?php
/**
 * Created by PhpStorm.
 * User: alvarobanofos
 * Date: 21/2/16
 * Time: 21:10
 */

namespace App\Http\Controllers;
use App\AnswersModel;
use App\Http\UsersAppModel;
use App\QuestionsModel;
use App\Revision;
use App\UserAnswerModel;
use Carbon\Carbon;
use Illuminate\Foundation\Auth\User;
use Illuminate\Http\Request;
use Mockery\CountValidator\Exception;


class ApiController extends Controller
{


    public function postSendAnswer(Request $request) {
        $now = time();
        $response["status"] = 1;
        $response["answerRegistered"] = false;
        try {
            $time = $request->get("time");
            $answerId = $request->get("answerId");
            $tokenUser = $request->get("tokenUser");
            dd($request->all());
            var_dump($now);
            var_dump($time);

            if($now - $time > 30) {
                $response["message"] = "Timeout: Tiempo de espera agotado";
                return $response;
            }

            $user = UsersAppModel::where("token", $tokenUser)->first();

            if(!$user) {
                $response["message"] = "Usuario incorrecto";
                return $response;
            }

            $answer = AnswersModel::where("id", $answerId)->first();

            if(!$answer) {
                $response["message"] = "Respuesta incorrecta";
                return $response;
            }

            $question = QuestionsModel::where("id", $answer->question_id)->first();

            $question_start = Carbon::createFromFormat("Y-m-d H:m:s", $question->time_ini)->timestamp;
            $question_end = $question_start + $question->duration;


            if($time < $question_start) {
                $response["message"] = "Pregunta no iniciada";
                return $response;
            }

            if($time > $question_end) {
                $response["message"] = "Pregunta finalizada";
                return $response;
            }

            $userAnswer = new UserAnswerModel();
            $userAnswer->question_id = $question->id;
            $userAnswer->answer_id = $answerId;
            $userAnswer->question_title = $question->title;
            $userAnswer->answer_title = $answer->title;
            $userAnswer->user_id = $user->id;
            $userAnswer->save();

            $response["answerRegistered"] = true;
            $response["user_answer"] = $userAnswer;

            return $response;

        }
        catch(\Exception $e) {
            $response["status"] = 0;
            $response["message"] = $e->getMessage();
            return $response;
        }
     }
    public function getGetImage($imageName) {
        $answer = AnswersModel::where("img_id", $imageName)->first();
        if(!$answer) {
            abort(401);
        }
        return \Image::make(storage_path() . '/app/answers_images/' . $answer->img_saved_name)->response();
    }

    public function postGetRevision() {
        $response["status"] = 1;
        try {
            $revision = Revision::where("id", 1)->first();
            return $revision;
        }
        catch(\Exception $e) {
            $response["status"] = 0;
            $response["message"] = $e->getMessage();
            return $response;
        }
    }
    public function postGetNextQuestions(Request $request) {
        $response["status"] = 1;
        try {
            $now = time();
            $timeMinus = $now - 120;
            $now = Carbon::createFromTimestamp($now, 'Europe/Madrid')->toDateTimeString();
            $timeMinus = Carbon::createFromTimestamp($timeMinus, 'Europe/Madrid')->toDateTimeString();
            $questions = QuestionsModel::where("time_ini", ">", $now)->orWhere(function($query) use ($now, $timeMinus)
            {
                $query->where('time_ini', '<', $now)
                    ->where('time_ini', '>', $timeMinus);
            })->get();
            $questions->load('answers');

            $result["questions"] = $questions;
            return json_encode($result);
        }
        catch(\Exception $e) {
            $response["status"] = 0;
            $response["message"] = $e->getMessage();
            return $response;
        }
    }
    public function postGetNextAnswers(Request $request) {
        $response["status"] = 1;
        try {
            $token = $request->get("token");
            $user = UsersAppModel::where("token", $token)->first();
            if(!$user) {
                abort(401);
            }
            $now = time();
            $now = Carbon::createFromTimestamp($now)->format("Y-m-d H:m:s");
            return QuestionsModel::where("time_ini", ">", $now)->get()->toJson();
        }
        catch(\Exception $e) {
            $response["status"] = 0;
            $response["message"] = $e->getMessage();
            return $response;
        }
    }

    public function postVerifyToken(Request $request) {
        $response["status"] = 1;
        $response["isValidToken"] = false;
        try {
            $email = $request->get("email");
            $token = $request->get("token");
            $user = UsersAppModel::where("email", $email)->where("token", $token)->first();
            if($user) {
                $response["isValidToken"] = true;
            }
            return $response;
        }
        catch(\Exception $e) {
            $response["status"] = 0;
            $response["message"] = $e->getMessage();
            return $response;
        }
    }
    public function postStandardLogin(Request $request) {
        $response["status"] = 1;
        $response["is_google_account"] = false;
        $response["registered"] = true;

        try {
            $email = $request->get("user");
            $pass = $request->get("pass");
            $user = UsersAppModel::where("email", $email)->first();
            if(!$user) {
                $response["registered"] = false;
                return $response;
            }

            if($user->google_id) {
                $response["is_google_account"] = true;
                return $response;
            }

            if($user->password != md5(sha1($pass))) {
                $response["registered"] = false;
                return $response;
            }

            $user->token = sha1(uniqid());
            $response["user"] = $user;
            $user->save();
            return $response;

            
        }
        catch(\Exception $e) {
            $response["status"] = 0;
            $response["message"] = $e->getMessage();
            return $response;
        }
    }

    public function postLoginByGoogle(Request $request) {

        $response["status"] = 1;

        try {


            //Check JSON valid structure
            if (!$request->exists("accountId") || !$request->get("accountId"))
                abort(400);

            if (!$request->exists("simId") || !$request->get("simId"))
                abort(400);

            $googleId = $request->get("accountId");
            $simId = $request->get("simId");


            $user = UsersAppModel::where("google_id", $googleId)->where("sim_id", $simId)->first();

            if (!$user) {
                $response["registered"] = false;
                return $response;
            }


            $response["registered"] = true;
            $user->token = sha1(uniqid());
            $user->save();
            $response["user"] = $user;

            return $response;

        }
        catch(\Exception $e) {
            $response["status"] = 0;
            $response["message"] = $e->getMessage();
            return $response;
        }

    }

    public function postRegisterFromGoogle(Request $request) {

        try {
            $response = [
                "status" => 1,
                "alreadyRegistered" => false
            ];
            if (!$this->validRegisterFromGoogleRequest($request)) {
                abort(400);
            }

            $googleId = $request->get("google_id");

            $user = UsersAppModel::existsGoogleAccount($googleId);

            if(!$user && UsersAppModel::existsSimAccount($request->get("sim_id"))) {
                $response["alreadyRegistered"] = true;
                return $response;
            }



            $birthdate = Carbon::createFromFormat("d/m/Y", $request->get("birthdate"))->toDateString();

            $userRegistrated = RegistrationController::getInstance()->registerUser(
                $request->get("email"),
                $request->get("gender"),
                $request->get("provincia"),
                $request->get("ciudad"),
                $birthdate,
                $request->get("sim_id"),
                $request->get("google_id"),
                $user
            );

            if(!$userRegistrated) {
                throwException(new \Exception("User registration failed!", 1001));
            }

            $response["user"] = $userRegistrated;

            return $response;




        }
        catch(\Exception $e) {
            $response["status"] = 0;
            $response["message"] = $e->getMessage();
            return $response;
        }





    }

    private function validRegisterFromGoogleRequest(Request $request) {
        $result = false;
        if(
            $request->exists("google_id") &&
            $request->exists("email") &&
            $request->exists("gender") &&
            $request->exists("birthdate") &&
            $request->exists("provincia") &&
            $request->exists("ciudad") &&
            $request->exists("sim_id")

        ){
            $result = true;
        }

        return $result;
    }




}

