<?php

declare(strict_types=1);

namespace TightenCo\Jigsaw\Scaffold;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;
use Illuminate\Support\Collection;
use JsonSerializable;
use Symfony\Component\Finder\SplFileInfo;
use TightenCo\Jigsaw\File\Filesystem;
use Traversable;

class PresetScaffoldBuilder extends ScaffoldBuilder
{
    /** @var PresetPackage */
    public $package;

    /** @var Filesystem */
    protected $files;

    /** @var ProcessRunner */
    protected $process;

    /** @deprecated unused */
    protected $question;

    /** @var string[] */
    protected $composerDependencies = [];

    public function __construct(Filesystem $files, PresetPackage $package, ProcessRunner $process)
    {
        $this->files = $files;
        $this->package = $package;
        $this->process = $process;
        $this->setBase();
    }

    public function init(?string $preset): ScaffoldBuilder
    {
        $this->package->init($preset ?? '', $this);
        $this->addPackageToCachedComposerRequires();

        return $this;
    }

    public function build(): ScaffoldBuilder
    {
        $this->package->runInstaller($this->console);

        return $this;
    }

    public function buildBasicScaffold(): PresetScaffoldBuilder
    {
        (new BasicScaffoldBuilder($this->files))
            ->setBase($this->base)
            ->build();

        return $this;
    }

    public function mergeComposerDotJson(): PresetScaffoldBuilder
    {
        $newComposer = collect($this->getComposer())
            ->forget(['name', 'type', 'version', 'description', 'keywords', 'license', 'authors'])
            ->toArray()
            ?? [];
        $merged = array_merge_recursive($this->composerCache ?? [], $newComposer);
        $this->writeComposer($this->preferVersionConstraintFromCached($merged));

        return $this;
    }

    /**
     * @param array|Collection|Arrayable|Jsonable|JsonSerializable|Traversable $match
     */
    public function deleteSiteFiles($match = []): PresetScaffoldBuilder
    {
        $matchCollection = collect($match);

        if ($matchCollection->count()) {
            collect($this->getSiteFilesAndDirectories($matchCollection->toArray()))
                ->each(function (SplFileInfo $file): void {
                    $source = $file->getPathName();

                    if ($this->files->isDirectory($file)) {
                        $this->files->deleteDirectory($source);
                    } elseif ($this->files->isFile($file)) {
                        $this->files->delete($source);
                    }
                });
        }

        return $this;
    }

    public function copyPresetFiles(array $match = [], array $ignore = [], ?string $directory = null): PresetScaffoldBuilder
    {
        $source = $this->package->path .
            ($directory ? DIRECTORY_SEPARATOR . trim($directory, '/') : '');

        collect($this->getPresetDirectories($match, $ignore, $source))
            ->each(function (SplFileInfo $directory): void {
                $destination = $this->base . DIRECTORY_SEPARATOR . $directory->getRelativePathName();

                if (! $this->files->exists($destination)) {
                    $this->files->makeDirectory($destination, 0755, true);
                }
            });

        collect($this->getPresetFiles($match, $ignore, $source))
            ->each(function (SplFileInfo $file): void {
                $source = $file->getPathName();
                $destination = $this->base . DIRECTORY_SEPARATOR . $file->getRelativePathName();
                $this->files->copy($source, $destination);
            });

        return $this;
    }

    public function runCommands(array $commands = []): PresetScaffoldBuilder
    {
        $this->process->run($commands);

        return $this;
    }

    protected function addPackageToCachedComposerRequires(): void
    {
        $this->composerDependencies[] = $this->package->vendor . DIRECTORY_SEPARATOR . $this->package->name;
    }

    protected function createDirectoryForFile(SplFileInfo $file): void
    {
        $path = $file->getRelativePath();

        if ($path && ! $this->files->isDirectory($path)) {
            $this->files->makeDirectory($path, 0755, true);
        }
    }

    protected function getSiteFilesAndDirectories(array $match = [], array $ignore = []): array
    {
        return $this->files->filesAndDirectories($this->base, $match, $ignore);
    }

    protected function getPresetDirectories(array $match, array $ignore, string $source): array
    {
        return $this->files->directories($source, $match, $ignore);
    }

    protected function getPresetFiles(array $match, array $ignore, string $source): array
    {
        return $this->files->files($source, $match, $ignore);
    }

    protected function preferVersionConstraintFromCached(array $composer): array
    {
        $require = collect(array_get($composer, 'require'))->mapWithKeys(function ($version, $package): array {
            return [$package => is_array($version) ? $version[0] : $version];
        });

        return array_set($composer, 'require', $require);
    }
}
