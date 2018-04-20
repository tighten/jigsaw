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

        $this->ifAlreadyScaffoldedWarnBeforeDoingTheFollowing(function () {
            $this->scaffoldSite();
            $this->scaffoldMix();
            $this->info('Site initialized successfully!');
        });
    }

    private function ifAlreadyScaffoldedWarnBeforeDoingTheFollowing($callback)
    {
        if ($this->files->exists($this->base . '/config.php')) {
            $this->info('It looks like you\'ve already run "jigsaw init" on this project.');
            $this->info('Running it again may overwrite important files.');
            $this->info('');

            if (! $this->confirm('Do you wish to continue? ')) {
                return;
            }
        }

        $callback();
    }

    private function scaffoldSite()
    {
        $this->files->copyDirectory(__DIR__ . '/../../stubs/site', $this->base);
    }

    private function scaffoldMix()
    {
        $this->files->copyDirectory(__DIR__ . '/../../stubs/mix', $this->base);
    }
}
