<?php

declare(strict_types=1);

namespace TightenCo\Jigsaw\Scaffold;

use TightenCo\Jigsaw\Console\ConsoleSession;

class CustomInstaller
{
    /** @var string[] */
    public $ignore = ['init.php'];

    /** @var  ?string */
    protected $from;

    /** @var ScaffoldBuilder */
    protected $builder;

    /** @var ConsoleSession */
    protected $console;

    /** @deprecated unused */
    protected $question;

    public function setConsole($console): CustomInstaller
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

    public function copy($files = null): CustomInstaller
    {
        $this->builder->cacheComposerDotJson();
        $this->builder->copyPresetFiles($files, $this->ignore, $this->from);
        $this->builder->mergeComposerDotJson();

        return $this;
    }

    public function from($from = null): CustomInstaller
    {
        $this->from = $from;

        return $this;
    }

    public function ignore($files): CustomInstaller
    {
        $this->ignore = array_merge($this->ignore, collect($files)->toArray());

        return $this;
    }

    public function delete($files = null): CustomInstaller
    {
        $this->builder->cacheComposerDotJson();
        $this->builder->deleteSiteFiles($files);
        $this->builder->mergeComposerDotJson();

        return $this;
    }

    public function run($commands = null): CustomInstaller
    {
        $this->builder->runCommands($commands);

        return $this;
    }

    public function ask($question, $default = null, $options = null, $errorMessage = null): string
    {
        return $this->console->ask($question, $default, $options, $errorMessage);
    }

    public function confirm($question, $default = null, $errorMessage = null): bool
    {
        return $this->console->confirm($question, $default);
    }

    public function output($text = ''): CustomInstaller
    {
        $this->console->write($text);

        return $this;
    }

    public function info($text = ''): CustomInstaller
    {
        $this->console->info($text);

        return $this;
    }

    public function error($text = ''): CustomInstaller
    {
        $this->console->error($text);

        return $this;
    }

    public function comment($text = ''): CustomInstaller
    {
        $this->console->comment($text);

        return $this;
    }
}
