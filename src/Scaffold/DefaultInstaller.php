<?php

namespace TightenCo\Jigsaw\Scaffold;

use Illuminate\Support\Arr;

class DefaultInstaller
{
    const ALWAYS_IGNORE = [
        'build_*',
        'init.php',
        'node_modules',
        'vendor',
    ];
    const DEFAULT_COMMANDS = [
        'composer install',
        'npm install',
        'npm run dev',
    ];
    protected $commands;
    protected $delete;
    protected $ignore;
    protected $builder;

    public function install(ScaffoldBuilder $builder, $settings = [])
    {
        $this->builder = $builder;
        $this->delete = Arr::get($settings, 'delete', []);
        $this->ignore = array_merge(self::ALWAYS_IGNORE, Arr::get($settings, 'ignore', []));
        $commands = Arr::get($settings, 'commands');
        $this->commands = $commands !== null ? $commands : self::DEFAULT_COMMANDS;
        $this->execute();
    }

    public function execute()
    {
        return $this->builder
            ->buildBasicScaffold()
            ->cacheComposerDotJson()
            ->deleteSiteFiles($this->delete)
            ->copyPresetFiles(null, $this->ignore)
            ->mergeComposerDotJson()
            ->runCommands($this->commands);
    }
}
