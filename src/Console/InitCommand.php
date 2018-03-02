<?php namespace TightenCo\Jigsaw\Console;

use Symfony\Component\Console\Input\InputArgument;
use TightenCo\Jigsaw\File\Filesystem;

class InitCommand extends Command
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
            $this->base .= '/' . $base;
        }

        $this->scaffoldSite();

        $this->info('Site initialized successfully!');
    }

    private function scaffoldSite()
    {
        $this->files->copyDirectory(__DIR__ . '/../../stubs/site', $this->base);
    }
}
