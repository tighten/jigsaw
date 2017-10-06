<?php

namespace TightenCo\Jigsaw\Console;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Finder\Finder;
use TightenCo\Jigsaw\File\Filesystem;

class MakePostCommand extends Command
{
    private $files;

    public function __construct(Filesystem $files)
    {
        $this->files = $files;
        parent::__construct();
    }

    protected function configure()
    {
        $this->setName('make:post')
            ->setDescription('Create a new post factory')
            ->addArgument('name', InputArgument::REQUIRED, 'The name of the file you want to create')
            ->addOption('title', 't', InputOption::VALUE_OPTIONAL, 'The name of title of the post.', 'Your Own Title')
            ->addOption('description', 'd', InputOption::VALUE_OPTIONAL, 'The description of title of the post.', 'Your Own Description')
            ->addOption('layout', 'l', InputOption::VALUE_OPTIONAL, 'The layout your file should extend from', '_layouts.master')
            ->addOption('section', 's', InputOption::VALUE_OPTIONAL, 'The name of the section you file should use.', 'content');
    }

    protected function fire()
    {
    }

}
