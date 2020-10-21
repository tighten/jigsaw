<?php

namespace TightenCo\Jigsaw\View;

use Exception;
use Illuminate\Container\Container;
use Illuminate\Support\Str;
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

        if (class_exists($class = $this->guessClassName($component))) {
            return $class;
        }

        throw new Exception(
            "Unable to locate a class or view for component [{$component}]."
        );
    }

    public function guessClassName(string $component)
    {
        $componentPieces = array_map(function ($componentPiece) {
            return ucfirst(Str::camel($componentPiece));
        }, explode('.', $component));
        return 'Components\\'.implode('\\', $componentPieces);
    }
}
