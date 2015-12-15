<?php namespace Jigsaw\Tests;

use Illuminate\Container\Container;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

abstract class CommandTestCase extends \PHPUnit_Framework_TestCase
{
    /**
     * @param string $class
     * @param string $command
     * @param array $arguments
     * @return CommandTester
     */
    public function runCommand( $class, $command, array $arguments = [])
    {
        $container   = new Container();
        $application = new Application();
        $application->add($container[$class]);

        $command = $application->find($command);
        $commandTester = new CommandTester($command);

        $commandTester->execute( array_merge([ 'command' => $command->getName() ], $arguments) );
        return $commandTester;
    }
}
