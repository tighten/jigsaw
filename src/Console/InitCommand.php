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
            $this->createBaseDirectory();
        }else{
            $this->setBasePath( getcwd(), true );
        }

        $this->createSourceFolder();
        $this->createBaseConfig();
        $this->info('Site initialized successfully in ['. $this->base .']!');
    }

    private function createBaseDirectory()
    {
        if (! $this->files->isDirectory($this->base)) {
            $this->files->makeDirectory($this->base);
        }
    }

    /**
     * @return bool
     */
    private function createSourceFolder()
    {
        if (! $this->files->isDirectory($this->base . DIRECTORY_SEPARATOR . 'source')) {
            return $this->files->makeDirectory($this->base . DIRECTORY_SEPARATOR . 'source');
        }

        $this->output->writeLn('<error>[!]</error> The path [<comment>'. $this->base . DIRECTORY_SEPARATOR . 'source' .'</comment>] already exists, doing nothing and exiting.');
        exit(1);
    }

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
