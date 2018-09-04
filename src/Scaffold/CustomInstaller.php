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

    public function clean()
    {
        //
    }

    public function copy()
    {
        //
    }

    public function delete($files = null)
    {
        $this->builder->deleteSiteFiles($files);
    }

    public function from()
    {
        //
    }

    public function ignore($files)
    {
        $this->ignore = array_merge($this->ignore, collect($files)->toArray());
    }

    public function output()
    {
        //
    }

    public function run($commands = null)
    {
        //
    }
}
