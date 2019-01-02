<?php

declare(strict_types=1);

namespace TightenCo\Jigsaw\Scaffold;

use Exception;
use TightenCo\Jigsaw\Console\ConsoleSession;
use TightenCo\Jigsaw\File\Filesystem;

class PresetPackage
{
    const PRESETS = [
        'blog' => 'tightenco/jigsaw-blog-template',
        'docs' => 'tightenco/jigsaw-docs-template',
    ];

    /** @var string */
    public $name;

    /** @var string */
    public $nameShort;

    /** @var string */
    public $path;

    /** @var string */
    public $preset;

    /** @var string */
    public $suffix;

    /** @var string */
    public $vendor;

    /** @var PresetScaffoldBuilder */
    protected $builder;

    /** @var CustomInstaller */
    protected $customInstaller;

    /** @var DefaultInstaller */
    protected $defaultInstaller;

    /** @var Filesystem */
    protected $files;

    /** @var ProcessRunner */
    protected $process;

    /** @var string */
    public $shortName;

    public function __construct(DefaultInstaller $default, CustomInstaller $custom, ProcessRunner $process)
    {
        $this->defaultInstaller = $default;
        $this->customInstaller = $custom;
        $this->process = $process;
        $this->files = new Filesystem();
    }

    public function init(string $preset, PresetScaffoldBuilder $builder): void
    {
        $this->preset = $preset;
        $this->builder = $builder;
        $this->resolveNames();
        $this->resolvePath();
    }

    public function runInstaller(?ConsoleSession $console): void
    {
        if (! $this->files->exists($this->path . DIRECTORY_SEPARATOR . 'init.php')) {
            $this->runDefaultInstaller();
            return;
        }

        try {
            $init = $this->customInstaller->setConsole($console)->install($this->builder);
            $initFile = include $this->path . DIRECTORY_SEPARATOR . 'init.php';

            if (is_array($initFile) && count($initFile)) {
                $this->runDefaultInstaller($initFile);
            }
        } catch (InstallerCommandException $e) {
            throw $e;
        } catch (Exception $e) {
            throw new Exception("The 'init.php' file for this preset contains errors.");
        }
    }

    protected function runDefaultInstaller(array $settings = []): void
    {
        $this->defaultInstaller->install($this->builder, $settings);
    }

    protected function resolveNames(): void
    {
        $name = array_get(self::PRESETS, $this->preset, $this->preset);

        if (! str_contains($name, '/')) {
            throw new Exception("'{$name}' is not a valid package name.");
        }

        $parts = explode('/', $name, 3);
        $this->vendor = array_get($parts, 0);
        $this->name = array_get($parts, 1);
        $this->suffix = array_get($parts, 2);
        $this->shortName = $this->getShortName();
    }

    protected function getShortName(): string
    {
        return str_contains($this->preset, '/') ?
            explode('/', $this->preset)[1] :
            $this->preset;
    }

    protected function resolvePath(): void
    {
        $this->path = collect([$this->builder->base, 'vendor', $this->vendor, $this->name, $this->suffix])
            ->filter()
            ->implode('/');

        if (! $this->files->exists($this->path)) {
            $package = $this->vendor . DIRECTORY_SEPARATOR . $this->name;

            try {
                $this->installPackageFromComposer($package);
            } catch (Exception $e) {
                throw new Exception("The '{$package}' preset could not be found.");
            }
        }
    }

    protected function installPackageFromComposer(string $package): void
    {
        $this->process->run('composer require ' . $package);
    }
}
