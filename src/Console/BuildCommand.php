<?php namespace TightenCo\Jigsaw\Console;

use TightenCo\Jigsaw\Jigsaw;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class BuildCommand extends Command
{
    private $source;
    private $dest;
    private $jigsaw;

    public function __construct($jigsaw, $source, $dest)
    {
        $this->source = $source;
        $this->dest = $dest;
        $this->jigsaw = $jigsaw;
        parent::__construct();
    }

    protected function configure()
    {
        $this->setName('build')
            ->setDescription('Build your site.')
            ->addArgument('env', InputArgument::OPTIONAL, "What environment should we use to build?", 'local')
            ->addOption('pretty', null, InputOption::VALUE_REQUIRED, "Should the site use pretty URLs?", 'true');
    }

    protected function fire()
    {
        $env = $this->input->getArgument('env');

        $this->dest .= '_' . $env;

        $this->jigsaw->build($this->source, $this->dest, $env, [
            'pretty' => $this->input->getOption('pretty') !== 'false'
        ]);

        $this->info('Site built successfully!');
    }
}
