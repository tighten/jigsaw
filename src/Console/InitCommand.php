<?php namespace Jigsaw\Jigsaw\Console;

use Illuminate\Filesystem\Filesystem;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class InitCommand extends Command
{
    private $files;
    private $base;

    public function __construct(Filesystem $files)
    {
        $this->files = $files;
        parent::__construct();
    }

    protected function configure()
    {
        $this->setName('init')
            ->setDescription('Scaffold a new Jigsaw project.')
            ->addArgument(
                'name',
                InputArgument::OPTIONAL,
                'Where should we initialize this project?'
            );
    }

    protected function fire()
    {
        if ($base = $this->input->getArgument('name')) {
            $this->base = getcwd() . '/' . $base;
            $this->createBaseDirectory();
        }

        $this->createSourceFolder();
        $this->createBaseConfig();
        $this->createGitIgnore();
        $this->createMasterLayout();
        $this->createIndexTemplate();
        $this->initializeElixir();
        $this->createAssetsFolders();
        $this->createStylesheets();

        $this->info('Site initialized successfully!');
    }

    private function createBaseDirectory()
    {
        if (! $this->files->isDirectory($this->base)) {
            $this->files->makeDirectory($this->base);
        }
    }

    private function createSourceFolder()
    {
        $this->files->makeDirectory($this->base . '/source');
    }

    private function createBaseConfig()
    {
        $this->files->put($this->base . '/config.php', <<<EOT
<?php

return [
    'production' => false,
];
EOT
        );
    }

    private function initializeElixir()
    {
        $this->createPackageJson();
        $this->createGulpFile();
    }

    private function createPackageJson()
    {
        $this->files->put($this->base . '/package.json', <<<EOT
{
  "private": true,
  "devDependencies": {
    "gulp": "^3.8.8"
  },
  "dependencies": {
    "laravel-elixir": "^4.0.0",
    "gulp-shell": "^0.5.1"
  }
}
EOT
        );
    }

    private function createGulpFile()
    {
        $this->files->put($this->base . '/gulpfile.js', <<<EOT
var gulp = require('gulp');
var elixir = require('laravel-elixir');
var shell = require('gulp-shell');

elixir.config.assetsPath = 'source/_assets';
elixir.config.publicPath = 'source/assets';

gulp.task('jigsaw-build', shell.task(['jigsaw build']));

elixir(function(mix) {
    mix.sass('main.scss').task('jigsaw-build', 'source/**/*');
});

EOT
        );
    }

    private function createAssetsFolders()
    {
        $this->files->makeDirectory($this->base . '/source/_assets');
        $this->files->makeDirectory($this->base . '/source/_assets/sass');
        $this->files->makeDirectory($this->base . '/source/assets/');
        $this->files->makeDirectory($this->base . '/source/assets/css');
    }

    private function createGitIgnore()
    {
        $this->files->put($this->base . '/.gitignore', <<<EOT
/build_local/
/node_modules/
/vendor/
EOT
        );
    }

    private function createMasterLayout()
    {
        $this->files->makeDirectory($this->base . '/source/_layouts');
        $this->files->put($this->base . '/source/_layouts/master.blade.php', <<<EOT
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
        <meta http-equiv="x-ua-compatible" content="ie=edge">
        <link rel="stylesheet" href="/assets/css/main.css">
    </head>
    <body>
        @yield('body')
    </body>
</html>
EOT
        );
    }

    private function createIndexTemplate()
    {
        $this->files->put($this->base . '/source/index.blade.php', <<<EOT
@extends('_layouts.master')

@section('body')
<h1>Hello world!</h1>
@endsection
EOT
        );
    }

    private function createStylesheets()
    {
        $this->files->put($this->base . '/source/_assets/sass/main.scss', '');
        $this->files->put($this->base . '/source/assets/css/main.css', '');
    }
}
