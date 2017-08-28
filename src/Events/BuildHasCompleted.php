<?php namespace TightenCo\Jigsaw\Events;


use Illuminate\Container\Container;

class BuildHasCompleted
{
    /** @var  $app Container */
    public $app;

    public function __construct($app)
    {
        $this->app = $app;
    }
}