<?php

namespace TightenCo\Jigsaw\Scaffold;

class CustomInstaller
{
    public $ignore = [];
    protected $builder;

    public function install(ScaffoldBuilder $builder)
    {
        $this->builder = $builder;

        return $this;
    }

    public function ask()
    {
        //
    }

    public function copy($files = null)
    {
        /**
         * @todo: cache composer before and restore after each copy call
         */
        $this->builder->copyPresetFiles($files, $this->ignore);

        return $this;
    }

    public function delete($files = null)
    {
        $this->builder->deleteSiteFiles($files);

        return $this;
    }

    public function from()
    {
        //
    }

    public function ignore($files)
    {
        $this->ignore = array_merge($this->ignore, collect($files)->toArray());

        return $this;
    }

    public function output()
    {
        //
    }

    public function run($commands = null)
    {
        //
    }

    public function setup()
    {
        $this->builder->buildBasicScaffold();

        return $this;
    }
}
