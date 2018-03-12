<?php

namespace Tests;

use Symfony\Component\Console\Tester\CommandTester;
use TightenCo\Jigsaw\Console\MakeCommand;

class MakeCommandTest extends TestCase
{
    public function test_jigsaw_make_console_command_can_include_arbitrary_options()
    {
        $command = $this->app->make(MakeCommand::class);
        $command_tester = new CommandTester($command);
        $command_tester->execute([
            'template' => 'post',
            '--abc' => '123',
            '--def' => '456',
        ]);

        $this->assertEquals('123', $command_tester->getInput()->getOption('abc'));
        $this->assertEquals('456', $command_tester->getInput()->getOption('def'));
    }
}
