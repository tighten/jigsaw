<?php

namespace TightenCo\Jigsaw\View;

use Illuminate\Container\Container;
use Illuminate\View\DynamicComponent as BaseDynamnicComponent;

class DynamicComponent extends BaseDynamnicComponent
{
    protected function compiler()
    {
        if (! static::$compiler) {
            static::$compiler = new ComponentTagCompiler(
                Container::getInstance()->make('blade.compiler')->getClassComponentAliases(),
                Container::getInstance()->make('blade.compiler')->getClassComponentNamespaces(),
                Container::getInstance()->make('blade.compiler')
            );
        }

        return static::$compiler;
    }
}
