<?php

namespace TightenCo\Jigsaw\Action;

class Action
{

    public $actions = [];

    public function do($name, ...$args)
    {
        if (isset($this->actions[$name])) {
            foreach ($this->actions[$name] as $action) {
                $action(...$args);
            }
        };
    }

    public function add($name, $callback)
    {
        $this->actions[$name][] = $callback;
    }

}
