<?php

namespace Tests;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;
use PHPUnit\Framework\TestCase as PHPUnit;
use Symfony\Component\Finder\SplFileInfo;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class SnapshotsTest extends PHPUnit
{
    protected Filesystem $filesystem;

    protected function setUp(): void
    {
        parent::setUp();

        $this->filesystem = new Filesystem;
    }

    public function snapshots(): array
    {
        return collect((new Filesystem)->directories($this->source()))
            ->map(fn ($path) => basename($path))
            ->reject(fn ($name) => Str::endsWith($name, '_snapshot'))
            // Prepend the test command with JIGSAW_SNAPSHOTS=<snapshot-names> to run specific snapshot tests
            ->when(isset($_SERVER['JIGSAW_SNAPSHOTS']), fn ($directories) => $directories->filter(
                fn ($name) => in_array($name, explode(',', $_SERVER['JIGSAW_SNAPSHOTS']))
            ))
            ->mapWithKeys(fn ($name) => [$name => [$name]])
            ->all();
    }

    /**
     * @test
     * @group snapshots
     * @dataProvider snapshots
     */
    public function build(string $name)
    {
        $this->runBuild($name);

        $this->assertSnapshotMatches($name);
    }

    private function runBuild(string $name, string ...$arguments): void
    {
        // Delete the contents of the output directory in the source to clean up previous builds
        $this->filesystem->deleteDirectory($this->output($name), true);

        $jigsaw = realpath(implode(DIRECTORY_SEPARATOR, array_filter([__DIR__, '..', 'jigsaw'])));

        $build = new Process(['php', $jigsaw, 'build', ...$arguments, '-vvv'], $this->source($name));

        $build->run();

        if (! $build->isSuccessful()) {
            throw new ProcessFailedException($build);
        }
    }

    private function assertSnapshotMatches($name)
    {
        $this->assertDirectoryExists($this->output($name));

        $this->assertSame(
            collect($this->filesystem->allFiles($this->snapshot($name), true))
                ->map(fn ($file) => Str::after($file->getPathname(), $this->snapshot($name)))
                ->toArray(),
            collect($this->filesystem->allFiles($this->output($name), true))
                ->map(fn ($file) => Str::after($file->getPathname(), $this->output($name)))
                ->toArray(),
            "Output file structure does not match snapshot in '{$name}'.",
        );

        collect($this->filesystem->files($this->output($name), true))->map(function (SplFileInfo $file) use ($name) {
            $this->assertSame(
                file_get_contents(implode(DIRECTORY_SEPARATOR, array_filter([$this->snapshot($name), $file->getRelativePathname()]))),
                $file->getContents(),
                "Output file '{$file->getRelativePathname()}' does not match snapshot in '{$name}'.",
            );
        });
    }

    private function source(string $name = ''): string
    {
        return implode(DIRECTORY_SEPARATOR, array_filter([__DIR__, 'snapshots', $name]));
    }

    private function output(string $name): string
    {
        return implode(DIRECTORY_SEPARATOR, array_filter([__DIR__, 'snapshots', $name, 'build_local']));
    }

    private function snapshot(string $name): string
    {
        return implode(DIRECTORY_SEPARATOR, array_filter([__DIR__, 'snapshots', "{$name}_snapshot"]));
    }
}
