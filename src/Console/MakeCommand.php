<?php namespace TightenCo\Jigsaw\Console;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use TightenCo\Jigsaw\File\Filesystem;

class MakeCommand extends Command
{
    private $files;
    private $base;

    public function __construct(Filesystem $files)
    {
        $this->files = $files;
        $this->base = getcwd();
        parent::__construct();
    }

    protected function configure()
    {
        $this->setName('make')
            ->setDescription('Scaffold a new file.')
            ->addArgument('template', InputArgument::OPTIONAL, 'What template should be used for the new file?')
            ->allowAnyOption();
    }

    protected function fire()
    {
        /**
         * - Find the template (for 'post' or 'posts', search /posts, /_posts, /post, /_post ?)
         * - Copy the template, use default filename if none was specified
         * - Update YAML variables with options from console command
         * - Update special variables (e.g. dates)
         * - Respond with location of new file
         */
        $this->info('New file was created.');
    }
}
