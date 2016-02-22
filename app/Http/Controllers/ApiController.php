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


            $user = UsersAppModel::where("google_id", $googleId)->where("sim_id", bin2hex(sha1($simId)))->first();

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
        catch(Exception $e) {
            $response["status"] = 0;
            $response["message"] = $e->getMessage();
            return $response;
        }

    }
}