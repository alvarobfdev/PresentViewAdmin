<?php
/**
 * Created by PhpStorm.
 * User: alvarobanofos
 * Date: 21/2/16
 * Time: 21:10
 */

namespace App\Http\Controllers;
use App\Http\UsersAppModel;
use App\QuestionsModel;
use Carbon\Carbon;
use Illuminate\Foundation\Auth\User;
use Illuminate\Http\Request;
use Mockery\CountValidator\Exception;


class ApiController extends Controller
{
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
            $response["token"] = sha1(uniqid());
            $user->token = $response["token"];
            $user->save();

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

            if (UsersAppModel::existsGoogleAccount($googleId)) {
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
                $request->get("google_id")
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

