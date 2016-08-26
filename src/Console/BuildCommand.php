<?php namespace TightenCo\Jigsaw\Console;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use TightenCo\Jigsaw\Jigsaw;
use TightenCo\Jigsaw\PrettyOutputPathResolver;

class BuildCommand extends Command
{
    private $app;
    private $source;
    private $dest;

    public function __construct($app, $source, $dest)
    {
        $this->app = $app;
        $this->source = $source;
        $this->dest = $dest;
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

        if ($this->input->getOption('pretty') === 'true') {
            $this->app->instance('outputPathResolver', new PrettyOutputPathResolver);
        }

        $this->app->make(Jigsaw::class)->build($this->source, $this->dest, $env);

        $this->info('Site built successfully!');
    }
}
