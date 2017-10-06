<?php

namespace TightenCo\Jigsaw\Console;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use TightenCo\Jigsaw\File\Filesystem;

class MakePostCommand extends Command
{
    private $files;
    private $base;

    public function __construct(Filesystem $files)
    {
        $this->files = $files;
        $this->base = getcwd();
        parent::__construct();

        var_dump($this->base);
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
        $file = $this->files->getFile(__DIR__.'/stubs', '*.md');

        $this->updateLayoutKey($file)->updateSectionKey($file);

        $this->info("File created.");
    }

    /**
     * Apply the needed fixes to the filename.
     *
     * @param  string $fileName
     * @return string
     */
    private function normalizeFileName($fileName)
    {
        return strtolower($fileName);
    }

    /**
     * Update the layout key in the template file.
     *
     * @param  File $file
     * @return $this
     */
    private function updateLayoutKey($file)
    {
        $this->updateKey($file, 'DummyLayout', '_layouts.master');

        return $this;
    }

    /**
     * Update the section key in the template file.
     *
     * @param  File $file
     * @return $this
     */
    private function updateSectionKey($file)
    {
        $this->updateKey($file, 'DummySection', 'content');

        return $this;
    }

    /**
     * Update a given key name with a given value
     * @param  File $file
     * @param  string $name
     * @param  string $value
     * @return void
     */
    private function updateKey($file, $name, $value)
    {
        $contents = $this->files->get($file);

        $updated = str_replace($name, $value, $contents);

        file_put_contents($file->getRealPath(), $updated);
    }
}
