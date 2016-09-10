<?php

namespace TightenCo\Jigsaw;

use Illuminate\View\Factory;

class ViewFactory extends Factory
{

    /**
     * @inheritdoc
     */
    protected function getExtension($path)
    {
        if (preg_match('@(.+).blade.(\w+)@', $path)) {
            return 'blade.php';
        }

        return parent::getExtension($path);
    }
}