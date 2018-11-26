<?php

namespace TightenCo\Jigsaw\Scaffold;

class CustomInstaller
{
    public $ignore = ['init.php'];
    protected $from;
    protected $builder;
    protected $console;
    protected $question;

    public function setConsole($console)
    {
        $this->console = $console;

        return $this;
    }

    public function install(ScaffoldBuilder $builder)
    {
        $this->builder = $builder;

        return $this;
    }

    public function setup()
    {
        $this->builder->buildBasicScaffold();

        return $this;
    }

    public function copy($files = null)
    {
        $this->builder->cacheComposerDotJson();
        $this->builder->copyPresetFiles($files, $this->ignore, $this->from);
        $this->builder->mergeComposerDotJson();

        return $this;
    }

    public function from($from = null)
    {
        $this->from = $from;

        return $this;
    }

    public function ignore($files)
    {
        $this->ignore = array_merge($this->ignore, collect($files)->toArray());

        return $this;
    }

    public function delete($files = null)
    {
        $this->builder->cacheComposerDotJson();
        $this->builder->deleteSiteFiles($files);
        $this->builder->mergeComposerDotJson();

        return $this;
    }

    public function run($commands = null)
    {
        $this->builder->runCommands($commands);

        return $this;
    }

    public function ask($question, $default = null, $options = null, $errorMessage = null)
    {
        return $this->console->ask($question, $default, $options, $errorMessage);
    }

    public function confirm($question, $default = null, $errorMessage = null)
    {
        return $this->console->confirm($question, $default);
    }

    public function output($text = '')
    {
        $this->console->write($text);

        return $this;
    }

    public function info($text = '')
    {
        $this->console->info($text);

        return $this;
    }

    public function error($text = '')
    {
        $this->console->error($text);

        return $this;
    }

    public function comment($text = '')
    {
        $this->console->comment($text);

        return $this;
    }
}
