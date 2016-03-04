<?php
/**
 * Created by PhpStorm.
 * User: alvarobanofos
 * Date: 4/3/16
 * Time: 19:44
 */

namespace App\Http\Controllers;


use App\Http\UsersAppModel;

class RegistrationController extends Controller
{
    /**
     * @var RegistrationController|null
     */
    private static $instance = null;

    public function __construct()
    {
        self::$instance = $this;
    }


    /**
     * @return RegistrationController|null
     */
    public static function getInstance() {
        if(self::$instance == null) {
            self::$instance = new RegistrationController();
        }
        return self::$instance;
    }


    public function registerUser($email, $gender, $provincia, $ciudad, $birthdate, $sim_id, $google_id=null) {

        $user = new UsersAppModel();
        $user->email = $email;
        $user->gender = $gender;
        $user->provincia = $provincia;
        $user->ciudad = $ciudad;
        $user->birthdate = $birthdate;
        $user->sim_id = $sim_id;

        if($google_id != null) {
            $user->google_id = $google_id;
        }

        $user->token = sha1(uniqid());

        if($user->save())
            return $user;
        else return false;

    }





}