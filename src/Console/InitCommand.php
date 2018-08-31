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
    private $basicScaffold;
    private $files;
    private $presetScaffold;

    public function __construct(Filesystem $files, BasicScaffold $basicScaffold, PresetScaffold $presetScaffold)
    {
        $this->basicScaffold = $basicScaffold;
        $this->presetScaffold = $presetScaffold;
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
        return $this->input->getArgument('preset') ? $this->presetScaffold : $this->basicScaffold;
    }

    protected function fire()
    {
        if ($this->initHasAlreadyBeenRun()) {
            $response = $this->askUser();

            switch ($response) {
                case 'a':
                    $this->line()
                        ->comment('Archiving your existing site...');
                    break;

                case 'd':
                    $this->line();

                    if ($this->confirm('<fg=red>Are you sure you want to delete your existing site?</> (y/n) ')) {
                        $this->line()
                            ->comment('Deleting your existing site...');
                        break;
                    }

                default:
                    $this->line();

                    return;
            }
        }

        try {
            $scaffold = $this->getScaffold();
            $scaffold->build($this->input->getArgument('preset'));

            $suffix = $scaffold instanceof $this->presetScaffold ?
                " using the '" . $scaffold->packageNameShort . "' preset." :
                ' successfully.';

            $this->info(
                'Your new Jigsaw site was initialized' . $suffix
            )->line();
        } catch (Exception $e) {
            $this->error($e->getMessage())
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
            'a' => '<info>archive</info> your existing site, then initialize a new one (default)',
            'd' => '<info>delete</info> your existing site, then initialize a new one',
            'c' => '<info>cancel</info>',
        ];

        return $this->choice('What would you like to do?', $choices, 0);
    }
}
