<?php

namespace TightenCo\Jigsaw\Console;

use Exception;
use Symfony\Component\Console\Input\InputArgument;
use TightenCo\Jigsaw\File\Filesystem;
use TightenCo\Jigsaw\Scaffold\BasicScaffoldBuilder;
use TightenCo\Jigsaw\Scaffold\InstallerCommandException;
use TightenCo\Jigsaw\Scaffold\PresetScaffoldBuilder;

class InitCommand extends Command
{
    private $base;
    private $basicScaffold;
    private $files;
    private $presetScaffold;

    public function __construct(Filesystem $files, BasicScaffoldBuilder $basicScaffold, PresetScaffoldBuilder $presetScaffold)
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

        return $this;
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

    protected function fire()
    {
        $scaffold = $this->getScaffold()->setBase($this->base);

        try {
            $scaffold->init($this->input->getArgument('preset'));
        } catch (Exception $e) {
            $this->console->error($e->getMessage())->line();

            return;
        }

        if ($this->initHasAlreadyBeenRun()) {
            $response = $this->askUserWhatToDoWithExistingSite();
            $this->console->line();

            switch ($response) {
                case 'a':
                    $this->console->comment('Archiving your existing site...');
                    $scaffold->archiveExistingSite();
                    break;

                case 'd':
                    if ($this->console->confirm(
                        '<fg=red>Are you sure you want to delete your existing site?</>'
                    )) {
                        $this->console->comment('Deleting your existing site...');
                        $scaffold->deleteExistingSite();
                        break;
                    }

                    // no break
                default:
                    return;
            }
        }

        try {
            $scaffold->setConsole($this->console)->build();

            $suffix = $scaffold instanceof $this->presetScaffold && $scaffold->package ?
                " using the '" . $scaffold->package->shortName . "' preset." :
                ' successfully.';

            $this->console
                ->line()
                ->info('Your new Jigsaw site was initialized' . $suffix)
                ->line();
        } catch (InstallerCommandException $e) {
            $this->console
                ->line()
                ->error("There was an error running the command '" . $e->getMessage() . "'")
                ->line();
        }
    }

    protected function getScaffold()
    {
        return $this->input->getArgument('preset') ?
            $this->presetScaffold :
            $this->basicScaffold;
    }

    protected function initHasAlreadyBeenRun()
    {
        return $this->files->exists($this->base . '/config.php') ||
            $this->files->exists($this->base . '/source');
    }

    protected function askUserWhatToDoWithExistingSite()
    {
        $this->console
            ->line()
            ->comment("It looks like you've already run 'jigsaw init' on this project.")
            ->comment('Running it again will overwrite important files.')
            ->line();

        $choices = [
            'a' => '<info>archive</info> your existing site, then initialize a new one',
            'd' => '<info>delete</info> your existing site, then initialize a new one',
            'c' => '<info>cancel</info>',
        ];

        return $this->console->ask('What would you like to do?', 'a', $choices);
    }
}
