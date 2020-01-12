<?php

namespace Tests;

use org\bovigo\vfs\vfsStream;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use TightenCo\Jigsaw\Console\Command;
use TightenCo\Jigsaw\Jigsaw;

class CustomCommandTest extends TestCase
{
    /**
     * @test
     */
    public function custom_command_with_no_arguments()
    {
        $vfs = vfsStream::setup('virtual', null, []);
        $command = $this->app->make(CustomCommand::class);
        $command->setApplication(new Application());

        $console = new CommandTester($command);
        $console->execute([]);

        $this->assertContains('Command Tested', $console->getDisplay());
    }
}

class CustomCommand extends Command {
    protected function fire()
    {
        $this->console->info("Command Tested");
    }
}
