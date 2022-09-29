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
    public function componentClass(string $component)
    {
        $viewFactory = Container::getInstance()->make(Factory::class);

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

        if ($class = $this->findClassByComponent($component)) {
            return $class;
        }

        if (class_exists($class = $this->guessClassName($component))) {
            return $class;
        }

        $guess = collect($this->blade->getAnonymousComponentNamespaces())
            ->filter(function ($directory, $prefix) use ($component) {
                return Str::startsWith($component, $prefix . '::');
            })
            // This is the only line that differs from the overidden method, to add the underscore
            ->prepend('_components', $component)
            ->reduce(function ($carry, $directory, $prefix) use ($component, $viewFactory) {
                if (! is_null($carry)) {
                    return $carry;
                }

                $componentName = Str::after($component, $prefix . '::');

                if ($viewFactory->exists($view = $this->guessViewName($componentName, $directory))) {
                    return $view;
                }

                if ($viewFactory->exists($view = $this->guessViewName($componentName, $directory) . '.index')) {
                    return $view;
                }
            });

        if (! is_null($guess)) {
            return $guess;
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
        return 'Components\\' . $this->formatClassName($component);
    }

    /**
     * Guess the view name for the given component.
     *
     * @param  string  $name
     * @param  string  $prefix
     * @return string
     */
    public function guessViewName($name, $prefix = '_components.')
    {
        $prefix = Str::finish($prefix, '.');

        $delimiter = ViewFinderInterface::HINT_PATH_DELIMITER;

        if (Str::contains($name, $delimiter)) {
            return Str::replaceFirst($delimiter, $delimiter . $prefix, $name);
        }

        return $prefix . $name;
    }
}
