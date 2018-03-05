<?php

namespace TightenCo\Jigsaw\Console;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use TightenCo\Jigsaw\File\Filesystem;
use InvalidArgumentException;

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
            ->addOption('title', 't', InputOption::VALUE_OPTIONAL, 'The title of the post.', 'My New Post')
            ->addOption('description', 'd', InputOption::VALUE_OPTIONAL, 'The description of the post.', 'My New Description')
            ->addOption('layout', 'l', InputOption::VALUE_OPTIONAL, 'The layout your file should extend from', '_layouts.master')
            ->addOption('section', 's', InputOption::VALUE_OPTIONAL, 'The name of the section your file should use.', 'content')
            ->addOption('extension', 'e', InputOption::VALUE_OPTIONAL, 'The type of file you want to generate.', 'md')
            ->addOption('folder', 'f', InputOption::VALUE_OPTIONAL, 'The folder where the file should be created in the public path', '_posts');
    }

    protected function fire()
    {
        $this->generateFile();
    }

    private function getStub()
    {
        return $this->files->getFile(__DIR__."/stubs", "*.md");
    }

    private function getFileExtension()
    {
        if (! in_array($this->input->getOption('extension'), ['md', 'blade.md'])) {
            throw new InvalidArgumentException('Invalid file type');
        }

        return ".".$this->input->getOption('extension');
    }

    private function getFileName()
    {
        return str_slug(strtolower($this->input->getArgument('name'))) . $this->getFileExtension();
    }

    private function getFilePath()
    {
        $directory = public_path($this->input->getOption('folder'));

        if (! $this->files->isDirectory($directory)) {
            $this->files->makeDirectory($directory);
        }

        return $directory . '/' . $this->getFileName();
    }

    private function buildContent($stub)
    {
        $layout = $this->input->getOption('layout');
        $title = $this->input->getOption('title');
        $description = $this->input->getOption('description');
        $section = $this->input->getOption('section');

        $stubContent = $this->files->get($stub);

        $fileContent = str_replace(
                ['DummyLayout', 'DummyTitle', 'DummyDescription', 'DummySection'],
                [$layout, $title, $description, $section],
                $stubContent
        );

        return $fileContent;
    }

    private function generateFile()
    {
        if ($this->files->exists($this->getFilePath())) {
            $this->error('File already exists');
            return false;
        }

        $stub = $this->getStub();
        $content = $this->buildContent($stub);

        $this->files->put($this->getFilePath(), $content);

        $this->info("File created.");
    }
}
