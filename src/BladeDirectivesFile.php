<?php namespace TightenCo\Jigsaw;

use Illuminate\View\Compilers\BladeCompiler;

class BladeDirectivesFile
{
    protected $directives;

    public function __construct($file_path)
    {
        $this->directives = file_exists($file_path) ? include $file_path : [];
    }

    public function register(BladeCompiler $compiler)
    {
        collect($this->directives)->each(function ($callback, $directive) use ($compiler) {
            $compiler->directive($directive, $callback);
        });
    }
}
