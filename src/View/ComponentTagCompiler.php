<?php

namespace TightenCo\Jigsaw\View;

use Illuminate\Container\Container;
use Illuminate\Support\Str;
use Illuminate\View\Compilers\ComponentTagCompiler as BaseComponentTagCompiler;
use Illuminate\View\Factory;
use Illuminate\View\ViewFinderInterface;
use InvalidArgumentException;

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
        $viewFactory = Container::getInstance()[Factory::class];

        if (isset($this->aliases[$component])) {
            if (class_exists($alias = $this->aliases[$component])) {
                return $alias;
            }

            if ($viewFactory->exists($alias)) {
                return $alias;
            }

            throw new InvalidArgumentException(
                "Unable to locate class or view [{$alias}] for component [{$component}]."
            );
        }

        if (class_exists($class = $this->guessClassName($component))) {
            return $class;
        }

        if ($viewFactory->exists($view = $this->guessViewName($component))) {
            return $view;
        }

        throw new InvalidArgumentException(
            "Unable to locate a class or view for component [{$component}]."
        );
    }

    /**
     * Guess the class name for the given component.
     *
     * @param  string  $component
     * @return string
     */
    public function guessClassName(string $component)
    {
        $componentPieces = array_map(function ($componentPiece) {
            return ucfirst(Str::camel($componentPiece));
        }, explode('.', $component));
        return 'Components\\'.implode('\\', $componentPieces);
    }

    /**
     * Guess the view name for the given component.
     *
     * @param  string  $name
     * @return string
     */
    public function guessViewName($name)
    {
        $prefix = '_components.';

        $delimiter = ViewFinderInterface::HINT_PATH_DELIMITER;

        if (Str::contains($name, $delimiter)) {
            return Str::replaceFirst($delimiter, $delimiter.$prefix, $name);
        }

        return $prefix.$name;
    }
}
