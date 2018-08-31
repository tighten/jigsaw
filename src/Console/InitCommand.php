<?php

namespace TightenCo\Jigsaw\Console;

use \Exception;
use Symfony\Component\Console\Input\InputArgument;
use TightenCo\Jigsaw\File\Filesystem;
use TightenCo\Jigsaw\Scaffold\BasicScaffold;
use TightenCo\Jigsaw\Scaffold\PresetScaffold;

class InitCommand extends Command
{
    private $base;
    private $basic_scaffold;
    private $files;
    private $preset_scaffold;

    public function __construct(Filesystem $files, BasicScaffold $basic_scaffold, PresetScaffold $preset_scaffold)
    {
        $this->basic_scaffold = $basic_scaffold;
        $this->preset_scaffold = $preset_scaffold;
        $this->files = $files;
        $this->setBase();
        parent::__construct();
    }

    public function setBase($cwd = null)
    {
        $this->base = $cwd ?: getcwd();
    }

    protected function configure()
    {
        $this->setName('init')
            ->setDescription('Scaffold a new Jigsaw project.')
            ->addArgument(
                'preset',
                InputArgument::OPTIONAL,
                'Which preset should we use to initialize this project?'
            );
    }

    protected function getScaffold()
    {
        return $this->input->getArgument('preset') ? $this->preset_scaffold : $this->basic_scaffold;
    }

    protected function fire()
    {
        if ($this->initHasAlreadyBeenRun()) {
            $response = $this->askUser();

            switch ($response) {
                case 'a':
                    $this->info('archiving...');
                    break;

                case 'd':
                    $this->error('deleting...');
                    break;

                default:
                    return;
            }
        }

        try {
            $this->getScaffold()->build($this->input->getArgument('preset'));
            $this->line()
                ->info('Your new Jigsaw site was initialized successfully!')
                ->line();
        } catch (Exception $e) {
            $this->line()
                ->error($e->getMessage())
                ->line();
        }
    }

    protected function initHasAlreadyBeenRun()
    {
        return $this->files->exists($this->base . '/config.php') ||
            $this->files->exists($this->base . '/source');
    }

    protected function askUser()
    {
        $this->line()
            ->comment("It looks like you've already run 'jigsaw init' on this project.")
            ->comment('Running it again will overwrite important files.')
            ->line();

        $choices = [
            'a' => 'archive your existing site, then initialize a new one [default]',
            'd' => 'delete your existing site, then initialize a new one',
            'c' => 'cancel',
        ];

        return $this->choice('What would you like to do?', $choices, 0);
    }
}
