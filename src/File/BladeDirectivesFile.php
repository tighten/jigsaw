<?php

namespace TightenCo\Jigsaw\File;

use Illuminate\View\Compilers\BladeCompiler;

class BladeDirectivesFile
{
    protected $bladeCompiler;
    protected $directives;

    public function __construct($file_path, BladeCompiler $bladeCompiler)
    {
        $this->bladeCompiler = $bladeCompiler;
        $this->directives = file_exists($file_path) ? include $file_path : [];

        if (! is_array($this->directives)) {
            $this->directives = [];
        }
    }

    public function register()
    {
        collect($this->directives)->each(function ($callback, $directive) {
            $this->bladeCompiler->directive($directive, $callback);
        });
    }
}
