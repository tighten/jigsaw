<?php namespace TightenCo\Jigsaw\Event;

class EventBus
{
    public $before;
    public $after;

    public function __construct()
    {
        $this->before = collect();
        $this->after = collect();
    }

    public function before($task)
    {
        $this->before = $this->before->merge(collect($task));
    }

    public function after($task)
    {
        $this->after = $this->after->merge(collect($task));
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
