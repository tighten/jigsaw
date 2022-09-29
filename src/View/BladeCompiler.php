<?php

namespace TightenCo\Jigsaw\View;

use Illuminate\View\Compilers\BladeCompiler as BaseBladeCompiler;
use TightenCo\Jigsaw\View\ComponentTagCompiler;

class BladeCompiler extends BaseBladeCompiler
{
    /**
     * The array of anonymous component namespaces to autoload from.
     *
     * @var array
     */
    protected $anonymousComponentNamespaces = [];

    /**
     * Compile the component tags.
     *
     * @param  string  $value
     * @return string
     */
    protected function compileComponentTags($value)
    {
        if (! $this->compilesComponentTags) {
            return $value;
        }

        return (new ComponentTagCompiler(
            $this->classComponentAliases, $this->classComponentNamespaces, $this
        ))->compile($value);
    }

    /**
     * Get the registered anonymous component namespaces.
     *
     * @return array
     */
    public function getAnonymousComponentNamespaces()
    {
        return $this->anonymousComponentNamespaces;
    }
}
