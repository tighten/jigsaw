<?php namespace TightenCo\Jigsaw\Console;

use Symfony\Component\Console\Command\Command as SymfonyCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

abstract class Command extends SymfonyCommand
{
    protected $allowAnyOption = false;

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $this->input = $input;
        $this->output = $output;

        return (int) $this->fire();
    }

    public function allowAnyOption()
    {
        $this->allowAnyOption = true;

        return $this;
    }

    public function run(InputInterface $input, OutputInterface $output)
    {
        if ($this->allowAnyOption) {
            $this->addOptionsFromInput($input);
        }

        parent::run($input, $output);
    }

    protected function addOptionsFromInput($input)
    {
        collect($this->parseOptions($input))->each(function ($option) {
            if ($option && ! $this->getDefinition()->hasOption($option)) {
                $this->getDefinition()->addOption(new InputOption($option, null, InputOption::VALUE_REQUIRED));
            }
        });
    }

    protected function parseOptions($input)
    {
        preg_match_all('/--(.*?)=/', (String) $input, $options);

        return array_get($options, 1);
    }

    protected function info($string)
    {
        $this->output->writeln("<info>{$string}</info>");
    }

    protected function error($string)
    {
        $this->output->writeln("<error>{$string}</error>");
    }

    protected function comment($string)
    {
        $this->output->writeln("<comment>{$string}</comment>");
    }

    abstract protected function fire();
}
