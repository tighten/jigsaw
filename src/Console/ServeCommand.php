<?php namespace TightenCo\Jigsaw\Console;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ServeCommand extends Command
{
    public function __construct()
    {
        parent::__construct();
    }

    protected function configure()
    {
        $this->setName('serve')
            ->setDescription('Serve local site with php built-in server.')
            ->addArgument(
                'environment',
                InputArgument::OPTIONAL,
                'What environment should we serve?'
            )
            ->addOption(
                'port',
                'p',
                InputOption::VALUE_REQUIRED,
                'What port should we use?'
            );
    }

    protected function fire()
    {
        $environment = $this->input->getArgument('environment') ?: 'local';
        $port = $this->input->getOption('port') ?: '8000';

        $this->info("Server started on http://localhost:{$port}");

        passthru("php -S localhost:{$port} -t build_{$environment}");
    }
}
