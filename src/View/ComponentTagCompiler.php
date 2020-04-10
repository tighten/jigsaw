<?php

namespace TightenCo\Jigsaw\View;

use Exception;
use Illuminate\Container\Container;
use Illuminate\View\Compilers\ComponentTagCompiler as BaseComponentTagCompiler;
use Illuminate\View\Factory;

class ComponentTagCompiler extends BaseComponentTagCompiler
{
    /**
     * Get the component class for a given component alias.
     *
     * @param  string  $component
     * @return string
     *
     * @throws \InvalidArgumentException
     */
    protected function componentClass(string $component)
    {
        if (isset($this->aliases[$component])) {
            return $this->aliases[$component];
        }

        if (Container::getInstance()[Factory::class]
            ->exists($view = "_components.{$component}")) {
            return $view;
        }

        throw new Exception(
            "Unable to locate a class or view for component [{$component}]."
        );
    }
}
