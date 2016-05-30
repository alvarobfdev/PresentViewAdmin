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


    public function postRanking(Request $request) {
        $response["status"] = 0;

        try {
            $tokenUser = $request->get("userToken");
            $user = UsersAppModel::where("token", $tokenUser)->first();

            if(!$user) {
                $response["message"] = "Usuario incorrecto";
                return $response;
            }
            $user->user_id = $user->id;
            $ranking = UserAnswerModel::select(\DB::raw('count(*) as numQuestions, user_id'))
                ->groupBy('user_id')
                ->orderBy('numQuestions', 'desc')
                ->take(10)
                ->get();

            $ranking->load("user");


            $response["rankings"] = $ranking->toArray();

            $meInArray = false;
            $i = 1;
            foreach($response["rankings"] as &$ranking) {
                if($ranking["user"])
                    $ranking["user"] = mb_substr($ranking["user"]["name"], 0, 3, 'UTF-8').mb_substr($ranking["user"]["surname"], -3, null, 'UTF-8');
                else $ranking["user"] = "Desconocido";

                $ranking["position"] = $i;
                $i++;
                if($ranking["user_id"] == $user->id) {
                    $ranking["me"] = true;
                    $meInArray = true;
                }
            }

            if(!$meInArray) {

                $userAns = \DB::select(
                    "SELECT user_id, count(*) as numQuestions,
	FIND_IN_SET(
        count(*), (SELECT GROUP_CONCAT(questions) FROM (SELECT count(*) as questions FROM app_answers GROUP BY user_id ORDER BY questions DESC) q)) as position FROM app_answers WHERE user_id=".$user->id." GROUP BY user_id"
                );
                if(count($userAns)>0) {
                    $userAns[0]->me = true;
                    $userAns[0]->user = substr($user->name, 0, 3).substr($user->surname, -3);
                    $response["rankings"][] = $userAns[0];
                }
            }
            $response["status"] = 1;
            return $response;
        }
        catch(\Exception $e) {
            $response["status"] = 0;
            $response["message"] = $e->getMessage();
            return $response;
        }
    }

    public function postSendAnswer(Request $request) {
        $now = time();
        $response["status"] = 1;
        $response["answerRegistered"] = false;
        try {
            $time = $request->get("time");
            $answerId = $request->get("answerId");
            $tokenUser = $request->get("tokenUser");


            if($now - $time > 30) {
                $response["message"] = "Timeout: Tiempo de espera agotado";
                return $response;
            }

            $user = UsersAppModel::where("token", $tokenUser)->first();

            if(!$user) {
                $response["message"] = "Usuario incorrecto";
                return $response;
            }
            $user->user_id = $user->id;
            $answer = AnswersModel::where("id", $answerId)->first();

            if(!$answer) {
                $response["message"] = "Respuesta incorrecta";
                return $response;
            }

            $question = QuestionsModel::where("id", $answer->question_id)->first();

            $question_start = Carbon::createFromFormat("Y-m-d H:i:s", $question->time_ini, 'Europe/Madrid')->timestamp;

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
            $user = UsersAppModel::where("token", $request->get("token"))->first();
            if(!$user) {
                $response["status"] = 0;
                $response["message"] = "Usario incorrecto!";
                return $response;
            }
            $now = time();
            $timeMinus = $now - 120;
            $now = Carbon::createFromTimestamp($now, 'Europe/Madrid')->toDateTimeString();
            $timeMinus = Carbon::createFromTimestamp($timeMinus, 'Europe/Madrid')->toDateTimeString();
            $questions = QuestionsModel::where("time_ini", ">", $now)->orWhere(function($query) use ($now, $timeMinus)
            {
                $query->where('time_ini', '<', $now)
                    ->where('time_ini', '>', $timeMinus);
            })->orWhere("winner_user_id", $user->id)
                ->get();
            $questions->load('answers');

            foreach($questions as &$question) {
                if($question->isFinished()) {
                    $question->finished = true;
                }
                foreach($question->answers as &$answer) {
                    $answer->percentage = $answer->getPercentage();
                }

                if($question->prize == 1){
                    $question->prize = true;
                }
                else $question->prize = false;

                if($question->winner == 1){
                    $question->winner = true;
                }
                else $question->winner = false;
            }

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
            $user->user_id = $user->id;
            $now = time();
            $now = Carbon::createFromTimestamp($now)->format("Y-m-d H:i:s");
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
            $user->user_id = $user->id;
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

            $user->user_id = $user->id;

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
            $user->user_id = $user->id;

            return $response;

        }
        catch(\Exception $e) {
            $response["status"] = 0;
            $response["message"] = $e->getMessage();
            return $response;
        }

    }

    public function postStandardRegistration(Request $request) {

        try {
            $response = [
                "status" => 1,
                "registered_yet" => false,
                "registrated" => false,
                "not_data_completed" => false
            ];


            $user = UsersAppModel::where("email", $request->get("email"))->first();

            var_dump($user);
            if ($user) {
                $response["registered_yet"] = true;
                if(!$user->name) {
                    $response["not_data_completed"] = true;
                }
                return $response;
            }

            $user = new UsersAppModel();
            $user->email = $request->get("user");
            $user->password = md5(sha1($request->get("pass")));
            $user->sim_id = $request->get("simId");
            $user->save();

            $response["registrated"] = true;
            return $response;
        }
        catch(\Exception $e) {
            $response["status"] = 0;
            $response["message"] = $e->getMessage();
            return $response;
        }


    }


    public function postStandardRegistrationComplete(Request $request) {

        try {
            $response = [
                "status" => 1,
                "alreadyRegistered" => false,
                "registered_ok" => false
            ];


            $user = UsersAppModel::where("email", $request->get("email"))->first();


            if (!$user) {
                $response["registered_ok"] = false;
                $response["message"] = "Fallo al registrar! Vuelva a intentarlo en unos instantes";
                return $response;
            }


            $user->gender = $request->get("gender");
            $birthdate = Carbon::createFromFormat("d/m/Y", $request->get("birthdate"))->toDateString();
            $user->birthdate = $birthdate;
            $user->provincia = $request->get("provincia");
            $user->ciudad = $request->get("ciudad");
            $user->name = $request->get("name");
            $user->surname = $request->get("surname");
            $user->token = sha1(uniqid());


            $user->save();

            $response["user"] = $user;
            $response["registered_ok"] = true;
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
                $user,
                $request->get("name"),
                $request->get("surname")
            );

            if(!$userRegistrated) {
                throwException(new \Exception("User registration failed!", 1001));
            }

            $userRegistrated->user_id = $userRegistrated->id;
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


    public function getAddRandomUsers($result) {
        $json = file_get_contents('http://api.randomuser.me/?nat=es&results='.$result);
        $json = json_decode($json);

        $jsonMunicipios = file_get_contents("http://abf-ubuntu.cloudapp.net/PresentViewAdmin/public/codmun.json");
        $jsonMunicipios = json_decode($jsonMunicipios, true);


        foreach($json->results as $person) {
            $user = new UsersAppModel();
            $user->name = $person->name->first;
            $user->surname = $person->name->last;
            $user->email = $person->email;
            if($person->gender == "male") {
                $user->gender = 0;
            }
            else $user->gender = 1;

            $rand = rand(0, count($jsonMunicipios)-1);
            $idmun = $jsonMunicipios[$rand]["CMUN"];
            $idprov = $jsonMunicipios[$rand]["CPRO"];

            $user->provincia = $idprov;
            $user->ciudad = $idmun;

            $intDate= rand(-1073001600,1073260800);

            $user->birthdate = Carbon::createFromTimestamp($intDate)->format("Y-m-d");
            $user->sim_id=rand(11111, 99999);
            $user->save();
        }
    }



}

