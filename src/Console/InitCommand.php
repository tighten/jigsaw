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
            $this->setBasePath( getcwd() . DIRECTORY_SEPARATOR . $base );
            // Create Base Directory
            $this->createFolder( $this->base );
        }else{
            $this->setBasePath( getcwd(), true );
        }

        // Create Source Folder
        $this->createFolder( $this->base . DIRECTORY_SEPARATOR . 'source' );
        $this->createBaseConfig();
        $this->info('Site initialized successfully in ['. $this->base .']!');
    }

    /**
     * Creates a folder at $path or exits with error code upon the folder existing
     * @param string $path
     * @return bool
     */
    private function createFolder( $path )
    {
        if (! $this->files->isDirectory($path)) {
            return $this->files->makeDirectory($path);
        }

        $this->output->writeLn('<error>[!]</error> The path [<comment>'. $path .'</comment>] already exists, doing nothing and exiting.');
        exit(1);
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
     * @return void
     */
    private function setBasePath( $path, $force = false )
    {
        if ( false === $force && $this->files->exists( $path ) )
        {
            $this->output->writeLn('<error>[!]</error> The path [<comment>'. $path .'</comment>] already exists, doing nothing and exiting.');
            exit(1);
        }

        $this->base = $path;
    }
}
