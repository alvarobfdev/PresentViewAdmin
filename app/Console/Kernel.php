<?php

namespace App\Console;

use App\QuestionsModel;
use App\Revision;
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
            $sleepQuestion->finished = 1;
            $sleepQuestion->save();
            Revision::updateRevision();

        }



        $now = time();
        dd("HERE");
        if(!$oneMinuteLess)
            $oneMinuteLess = Carbon::parse($now-60, 'Europe/Madrid')->format('Y-m-d H:i:s');
        if(!$oneMinuteMore)
            $oneMinuteMore = Carbon::parse($now+60, 'Europe/Madrid')->format('Y-m-d H:i:s');

        $questions = QuestionsModel::where("time_ini", ">=", $oneMinuteLess)
            ->where("time_ini", "<=", $oneMinuteMore)
            ->where("finished", 0)
            ->get();

        $sleepTime = null;
        $sleepQuestion = null;
        foreach($questions as $question) {
            $time_ini = Carbon::createFromFormat('Y-m-d H:i:s', $question->time_ini)->timestamp;
            $time_end = $time_ini + $question->duration;


            if($time_end > time()) {
                $sleep = time() - $time_end;
                if(!$sleepTime || $sleepTime < $sleep) {
                    $sleepTime = $sleep;
                    $sleepQuestion = $question;
                }
            }
            else {
                $question->finished = 1;
                $question->save();
                Revision::updateRevision();
            }
        }

        if($sleepTime) {
            sleep($sleepTime);
            $this->controlFinishedQuestions($sleepQuestion, $oneMinuteLess, $oneMinuteMore);
        }

    }
}
