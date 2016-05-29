<?php
/**
 * Created by PhpStorm.
 * User: alvarobanofos
 * Date: 31/1/16
 * Time: 12:09
 */

namespace App\Http\Controllers;


use App\AnswersModel;
use App\Http\UsersAppModel;
use App\QuestionsModel;
use App\Revision;
use App\UserAnswerModel;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;

class QuestionsController extends Controller
{
    public function getIndex(Request $request) {

        $questions = QuestionsModel::orderBy('time_ini', 'desc')->get();
        $data["questions"] = $questions;
        return view("questions.index", $data);
    }

    public function getAdd(Request $request) {
        return view("questions.add");
    }

    public function postAdd(Request $request) {

        $fail = false;

        $validator = \Validator::make(\Request::all(), [
            'questionTitle' => 'required|min:10',
            'datetime'      => 'required|date_format:"d/m/Y H:i',
            'duration'      =>  'numeric|min:30|max:120',
            'prizeTitle'    =>  'string|min:5|required_with:activatePrize'
        ]);

        if($validator->fails()) {
            $fail = true;
        }

        if(!$request->exists("answer_title") || count($request->get("answer_title"))<2) {
            $fail = true;
            $validator->getMessageBag()->add("fail_answers", "Tiene que insertar mínimo dos posibles resupuestas");
        }

        if(!$fail)
            foreach($request->get("answer_title") as $answer) {
                if(strlen($answer) < 2) {
                    $fail = true;
                    $validator->getMessageBag()->add("fail_answers", "Las respuestas tienen que tener mínimo 2 caracteres");
                }
            }


        if($fail) {
            return redirect('questions/add')
                ->withErrors($validator)
                ->withInput();
        }

        $question = new QuestionsModel();

        $question->title = $request->get("questionTitle");
        $question->time_ini = Carbon::createFromFormat("d/m/Y H:i", $request->get("datetime"))->toDateTimeString();
        $question->duration = $request->get("duration");

        if($request->has("activatePrize")) {
            $question->prize = 1;
            $question->prize_title = $request->get("prizeTitle");
        }



        $answers = $request->get("answer_title");


        if(!$question->save()) {
            return redirect('questions/add')
                ->withErrors(["fail_bbdd" => "Errror grave 1001: Consulte al administrador"])
                ->withInput();
        }

        foreach($answers as $index=>$answerTitle) {
            $answer = new AnswersModel();
            $answer->title = $answerTitle;
            $answersImages = $request->file("answer_image");

            if($answersImages && array_key_exists($index, $answersImages) && $answersImages[$index]) {
                $pathUploadedFile = $answersImages[$index]->getPathName();
                $extension = $answersImages[$index]->getClientOriginalExtension();
                $imageName = uniqid();
                $imageExtension = $imageName.".".$extension;
                \Image::make($pathUploadedFile)->resize(100, null, function ($constraint) {
                    $constraint->aspectRatio();
                })->save(storage_path("app/answers_images/$imageExtension"));
                $answer->img_uri = url('api/get-image/'.$imageName);
                $answer->img_id = $imageName;
                $answer->img_saved_name = $imageExtension;
            }

            if(!$question->answers()->save($answer)) {
                $question->delete();
                return redirect('questions/add')
                    ->withErrors(["fail_bbdd" => "Errror grave 1002: Consulte al administrador"])
                    ->withInput();
            }

        }

        $result = Revision::updateRevision();

        if($result != "success") {
            $question->delete();
            return redirect('questions/add')
                ->withErrors($result)
                ->withInput();
        }

        return redirect('questions')->withOk("Pregunta registrada con éxito");




    }





}