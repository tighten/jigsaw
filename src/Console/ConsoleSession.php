<?php

declare(strict_types=1);

namespace TightenCo\Jigsaw\Console;

use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Question\Question;

class ConsoleSession
{
    /** @var InputInterface */
    protected $input;

    /** @var OutputInterface */
    protected $output;

    /** @var QuestionHelper */
    protected $question;

    public function __construct(InputInterface $input, OutputInterface $output, QuestionHelper $question)
    {
        $this->input = $input;
        $this->output = $output;
        $this->question = $question;
    }

    public function write(string $string): ConsoleSession
    {
        $this->output->writeln($string);

        return $this;
    }

    public function info(string $string): ConsoleSession
    {
        return $this->write("<info>{$string}</info>");
    }

    public function error(string $string): ConsoleSession
    {
        return $this->write("<fg=red>{$string}</>");
    }

    public function comment(string $string): ConsoleSession
    {
        return $this->write("<comment>{$string}</comment>");
    }

    public function line(): ConsoleSession
    {
        return $this->write('');
    }

    public function ask(string $question, ?string $default = null, ?array $choices = null, string $errorMessage = ''): string
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

    public function confirm(string $question, bool $default = false, string $errorMessage = ''): bool
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
