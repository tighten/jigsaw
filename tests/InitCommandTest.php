<?php namespace Jigsaw\Tests;

use Jigsaw\Jigsaw\Console\InitCommand;

class InitCommandTest extends CommandTestCase
{

    public function testDefaultInit()
    {

        $output = $this->runCommand( InitCommand::class, 'init' );

        var_dump($output->getDisplay());

    }

}
