<?php

declare(strict_types=1);

namespace TightenCo\Jigsaw\File;

use Illuminate\View\Compilers\BladeCompiler;

class BladeDirectivesFile
{
    /** @var BladeCompiler */
    protected $bladeCompiler;

    /** @var array */
    protected $directives;

    public function __construct(string $file_path, BladeCompiler $bladeCompiler)
    {
        $this->bladeCompiler = $bladeCompiler;
        $this->directives = file_exists($file_path) ? include $file_path : [];

        if (! is_array($this->directives)) {
            $this->directives = [];
        }
    }

    public function register(): void
    {
        collect($this->directives)->each(function ($callback, $directive): void {
            $this->bladeCompiler->directive($directive, $callback);
        });
    }
}
