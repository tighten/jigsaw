<?php

namespace TightenCo\Jigsaw\View;

use Illuminate\View\Compilers\BladeCompiler as BaseBladeCompiler;
use TightenCo\Jigsaw\View\ComponentTagCompiler;

class BladeCompiler extends BaseBladeCompiler
{
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
            $this->classComponentAliases, $this
        ))->compile($value);
    }
}
