<?php

declare(strict_types=1);

namespace TightenCo\Jigsaw\Events;

use Illuminate\Support\Collection;
use TightenCo\Jigsaw\Jigsaw;

class EventBus
{
    /** @var Collection */
    public $beforeBuild;

    /** @var Collection */
    public $afterCollections;

    /** @var Collection */
    public $afterBuild;

    public function __construct()
    {
        $this->beforeBuild = collect();
        $this->afterCollections = collect();
        $this->afterBuild = collect();
    }

    public function __call($event, $arguments): void
    {
        if (isset($this->{$event})) {
            $this->{$event} = $this->{$event}->merge(collect($arguments[0]));
        }
    }

    public function fire(string $event, Jigsaw $jigsaw): void
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
