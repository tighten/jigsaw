<?php namespace Jigsaw\Jigsaw\Console;

use Illuminate\Filesystem\Filesystem;
use Symfony\Component\Console\Input\InputArgument;

class InitCommand extends Command
{
    /**
     * @var Filesystem
     */
    private $files;

    /**
     * @var string the current working directory
     */
    private $base;

    /**
     * InitCommand constructor.
     * @param Filesystem $files
     */
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
            if (!$this->setBasePath(getcwd() . DIRECTORY_SEPARATOR . $base)) {
                return 1;
            }
            // Create Base Directory
            if (!$this->createFolder($this->base)) {
                return 1;
            }
        } else {
            if (!$this->setBasePath(getcwd(), true)) {
                return 1;
            }
        }

        // Create Source Folder
        if (!$this->createFolder($this->base . DIRECTORY_SEPARATOR . 'source')) {
            return 1;
        }
        $this->createBaseConfig();
        $this->info('Site initialized successfully in [' . $this->base . ']!');

        return 0;
    }

    /**
     * Creates a folder at $path or exits with error code upon the folder existing
     * @param string $path
     * @return bool
     */
    private function createFolder($path)
    {
        if (!$this->files->isDirectory($path)) {
            return $this->files->makeDirectory($path);
        }

        $this->outputPathExistsError($path);
        return false;
    }

    /**
     * Create the initial config.php file
     * @return void
     */
    private function createBaseConfig()
    {
        $this->files->put($this->base . DIRECTORY_SEPARATOR . 'config.php', <<<EOT
<?php

return [
    'production' => false,
];
EOT
        );
    }

    /**
     * Check that the $path does not already exist before setting base.
     * @param string $path
     * @param bool $force allow for the base to be current directory when name argument is not passed
     * @return bool
     */
    private function setBasePath($path, $force = false)
    {
        if (false === $force && $this->files->exists($path)) {
            $this->outputPathExistsError($path);
            return false;
        }

        $this->base = $path;
        return true;
    }

    private function outputPathExistsError($path)
    {
        $this->output->writeLn('<error>[!]</error> The path [<comment>' . $path . '</comment>] already exists, doing nothing and exiting.');
    }
}
