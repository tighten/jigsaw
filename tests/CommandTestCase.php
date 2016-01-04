<?php namespace Jigsaw\Tests;

use Illuminate\Container\Container;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;

abstract class CommandTestCase extends \PHPUnit_Framework_TestCase
{
    /**
     * @param string $class
     * @param string $command
     * @param array $arguments
     * @return CommandTester
     */
    public function runCommand($class, $command, array $arguments = [])
    {
        $application = new Application();
        $application->setAutoExit(false);

        if ($class instanceof Command) {
            $application->add($class);
        } else {
            $container = new Container();
            $application->add($container[$class]);
        }

        $command = $application->find($command);
        $commandTester = new CommandTester($command);
        $arguments = array_merge(['command' => $command->getName()], $arguments);

        $commandTester->execute($arguments);
        return $commandTester;
    }
}
