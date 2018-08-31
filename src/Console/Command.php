<?php namespace TightenCo\Jigsaw\Console;

use Symfony\Component\Console\Command\Command as SymfonyCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\ConfirmationQuestion;

abstract class Command extends SymfonyCommand
{
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $this->input = $input;
        $this->output = $output;
        return (int) $this->fire();
    }

    protected function confirm($question, $default = false)
    {
        if ($this->getHelper('question')->ask(
            $this->input,
            $this->output,
            new ConfirmationQuestion($question, $default)
        )) {
            return true;
        }
    }

    protected function choice($question, $choices, $default = false, $error = '')
    {
        $question = new ChoiceQuestion($question, $choices, $default);
        $question->setErrorMessage($error ?: "Selection '%s' is invalid.");

        return $this->getHelper('question')->ask(
            $this->input,
            $this->output,
            $question
        );
    }

    protected function info($string)
    {
        $this->output->writeln("<info>{$string}</info>");

        return $this;
    }

    protected function error($string)
    {
        $this->output->writeln("<fg=red>{$string}</>");

        return $this;
    }

    protected function comment($string)
    {
        $this->output->writeln("<comment>{$string}</comment>");

        return $this;
    }

    protected function line()
    {
        $this->output->writeln('');

        return $this;
    }

    abstract protected function fire();
}
