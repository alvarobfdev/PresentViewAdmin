<?php
/**
 * Created by PhpStorm.
 * User: alvarobanofos
 * Date: 30/1/16
 * Time: 13:15
 */

namespace App\Http\Controllers;


class HomeController extends Controller
{
    public function getIndex() {
        return view("home");
    }
}