<?php namespace TightenCo\Jigsaw\Events;

use TightenCo\Jigsaw\Jigsaw;

class EventBus
{
    public $start;
    public $beforeBuild;
    public $afterBuild;

    public function __construct()
    {
        $this->start = collect();
        $this->beforeBuild = collect();
        $this->afterBuild = collect();
    }

    public function __call($event, $arguments)
    {
        if ($this->{$event}) {
            $this->{$event} = $this->{$event}->merge(collect($arguments[0]));
        }
    }

    public function fire($event, Jigsaw $jigsaw)
    {
        $this->{$event}->each(function ($task) use ($jigsaw) {
            if (is_callable($task)) {
                $task($jigsaw);
            } else {
                (new $task())->handle($jigsaw);
            }
        });
    }
}
