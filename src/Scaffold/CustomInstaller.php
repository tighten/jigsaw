<?php

declare(strict_types=1);

namespace TightenCo\Jigsaw\Scaffold;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;
use Illuminate\Support\Collection;
use JsonSerializable;
use TightenCo\Jigsaw\Console\ConsoleSession;
use Traversable;

class CustomInstaller
{
    /** @var string[] */
    public $ignore = ['init.php'];

    /** @var ?string */
    protected $from;

    /** @var ScaffoldBuilder */
    protected $builder;

    /** @var ?ConsoleSession */
    protected $console;

    /** @deprecated unused */
    protected $question;

    public function setConsole(?ConsoleSession $console): CustomInstaller
    {
        $this->console = $console;

        return $this;
    }

    public function install(ScaffoldBuilder $builder): CustomInstaller
    {
        $this->builder = $builder;

        return $this;
    }

    public function setup(): CustomInstaller
    {
        $this->builder->buildBasicScaffold();

        return $this;
    }

    public function copy(?array $files = null): CustomInstaller
    {
        $this->builder->cacheComposerDotJson();
        $this->builder->copyPresetFiles($files ?? [], $this->ignore, $this->from);
        $this->builder->mergeComposerDotJson();

        return $this;
    }

    public function from(?string $from = null): CustomInstaller
    {
        $this->from = $from;

        return $this;
    }

    /**
     * @param array|Collection|Arrayable|Jsonable|JsonSerializable|Traversable $files
     */
    public function ignore($files): CustomInstaller
    {
        $this->ignore = array_merge($this->ignore, collect($files)->toArray());

        return $this;
    }

    public function delete(?array $files = null): CustomInstaller
    {
        $this->builder->cacheComposerDotJson();
        $this->builder->deleteSiteFiles($files);
        $this->builder->mergeComposerDotJson();

        return $this;
    }

    public function run(?array $commands = null): CustomInstaller
    {
        $this->builder->runCommands($commands);

        return $this;
    }

    public function ask(string $question, ?string $default = null, ?array $options = null, ?string $errorMessage = null): string
    {
        return $this->console->ask($question, $default, $options, $errorMessage ?? '');
    }

    public function confirm(string $question, bool $default = false, ?string $errorMessage = null): bool
    {
        return $this->console->confirm($question, $default);
    }

    public function output(string $text = ''): CustomInstaller
    {
        $this->console->write($text);

        return $this;
    }

    public function info(string $text = ''): CustomInstaller
    {
        $this->console->info($text);

        return $this;
    }

    public function error(string $text = ''): CustomInstaller
    {
        $this->console->error($text);

        return $this;
    }

    public function comment(string $text = ''): CustomInstaller
    {
        $this->console->comment($text);

        return $this;
    }
}
