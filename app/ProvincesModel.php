<?php
/**
 * Created by PhpStorm.
 * User: alvarobanofos
 * Date: 29/5/16
 * Time: 19:28
 */

namespace App;


class ProvincesModel
{
    public static function getProvinces() {
        $provinces_json = file_get_contents(public_path("codprov.json"));
        $provinces_json = json_decode($provinces_json);
        return $provinces_json;

    }
}