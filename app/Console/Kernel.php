<?php

namespace App\Console;

use App\Http\UsersAppModel;
use App\QuestionsModel;
use App\Revision;
use App\UserAnswerModel;
use Carbon\Carbon;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        Commands\Inspire::class,
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->call(function () {
            $this->controlFinishedQuestions();
        })->everyMinute();
    }

    protected function controlFinishedQuestions($sleepQuestion = null, $oneMinuteLess=null, $oneMinuteMore=null) {

        if($sleepQuestion) {
            $this->finishQuestion($sleepQuestion);
        }



        $now = time();
        if(!$oneMinuteLess)
            $oneMinuteLess = Carbon::createFromTimestamp($now-65, 'Europe/Madrid')->format('Y-m-d H:i:s');
        if(!$oneMinuteMore)
            $oneMinuteMore = Carbon::createFromTimestamp($now+65, 'Europe/Madrid')->format('Y-m-d H:i:s');

        $questions = QuestionsModel::where("time_ini", ">=", $oneMinuteLess)
            ->where("time_ini", "<=", $oneMinuteMore)
            ->where("finished", 0)
            ->get();

        $sleepTime = null;
        foreach($questions as $question) {
            $time_ini = Carbon::createFromFormat('Y-m-d H:i:s', $question->time_ini, 'Europe/Madrid')->timestamp;
            $time_end = $time_ini + $question->duration;


            if($time_end > time()) {
                $sleep = $time_end - time();
                if(!$sleepTime || $sleepTime < $sleep) {
                    $sleepTime = $sleep;
                    $sleepQuestion = $question;
                }
            }
            else {
                $this->finishQuestion($question);
            }
        }

        if($sleepTime) {
            sleep($sleepTime);
            $this->controlFinishedQuestions($sleepQuestion, $oneMinuteLess, $oneMinuteMore);
        }

    }

    protected function addWinner($question_id) {
        $question = QuestionsModel::where("id", $question_id)->first();
        if($question->prize == 1 && $question->winner == 0){
            $answers = $question->userAnswers()->get()->toArray();

            if(count($answers) > 0) {
                $winnerPosition = rand(0, count($answers)-1);
                $winner = $answers[$winnerPosition];
                $question->winner = 1;
                $question->winner_user_id = $winner["user_id"];
                $user = $question->winnerUser()->first();
                $question->winner_name = $user->getShortUsername();
                $question->save();
            }
        }
    }

    private function randomAnswers(QuestionsModel $question) {
        $users = UsersAppModel::whereNull("token")->orderByRaw('RAND()')->take(100)->get();
        foreach($users as $user) {
            $answer = new UserAnswerModel();

            $answers = $question->answers()->get()->toArray();

            $randAnswer = rand(0, count($answers)-1);

            $selectedAnswer = $answers[$randAnswer];

            $answer->question_id = $question->id;
            $answer->answer_id = $selectedAnswer["id"];
            $answer->question_title = $question->title;
            $answer->answer_title = $selectedAnswer["title"];
            $answer->user_id = $user->id;

            $answer->save();

        }
    }

    /**
     * @param $sleepQuestion
     */
    protected function finishQuestion($sleepQuestion)
    {
        $question = QuestionsModel::where("id", $sleepQuestion->id)->first();
        if($question->finised == 0) {
            $sleepQuestion->finished = 1;
            $this->addWinner($sleepQuestion->id);
            $this->randomAnswers($sleepQuestion);
            $sleepQuestion->save();
            Revision::updateRevision();
        }
    }
}
