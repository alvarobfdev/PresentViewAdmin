<?php
/**
 * Created by PhpStorm.
 * User: alvarobanofos
 * Date: 21/2/16
 * Time: 21:10
 */

namespace App\Http\Controllers;
use Illuminate\Http\Request;



class ApiController extends Controller
{
    public function postLoginOrRegisterGoogle(Request $request) {
        return $request->get("jsonData");
    }
}