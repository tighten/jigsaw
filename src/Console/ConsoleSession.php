<?php

namespace TightenCo\Jigsaw\Console;

use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Question\Question;

class ConsoleSession
{
    protected $input;
    protected $output;
    protected $question;

    public function __construct(InputInterface $input, OutputInterface $output, QuestionHelper $question)
    {
        $this->input = $input;
        $this->output = $output;
        $this->question = $question;
    }

    public function write($string)
    {
        $this->output->writeln($string);

        return $this;
    }

    public function info($string)
    {
        return $this->write("<info>{$string}</info>");
    }

    public function error($string)
    {
        return $this->write("<fg=red>{$string}</>");
    }

    public function comment($string)
    {
        return $this->write("<comment>{$string}</comment>");
    }

    public function line()
    {
        return $this->write('');
    }

    public function ask($question, $default = null, $choices = null, $errorMessage = '')
    {
        $defaultPrompt = $default ? '<fg=blue>(default <fg=white>' . $default . '</>) </>' : '';

        if ($choices) {
            $question = new ChoiceQuestion($question . ' ' . $defaultPrompt, $choices, $default ?? false);
            $question->setErrorMessage($errorMessage ?: 'Selection "%s" is invalid.');
        } else {
            $question = new Question($question . ' ' . $defaultPrompt, $default ?? '');
        }

        return $this->question->ask(
            $this->input,
            $this->output,
            $question
        );
    }

    public function confirm($question, $default = false, $errorMessage = '')
    {
        $defaultPrompt = $default ?
            ' <fg=blue>(default <fg=white>y</>)</> ' :
            ' <fg=blue>(default <fg=white>n</>)</> ';

        return (bool) $this->question->ask(
            $this->input,
            $this->output,
            new ConfirmationQuestion($question . $defaultPrompt, $default ?? false)
        );
    }
}
