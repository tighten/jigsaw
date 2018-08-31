<?php

namespace Tests;

use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use TightenCo\Jigsaw\Console\InitCommand;
use TightenCo\Jigsaw\Scaffold\BasicScaffold;
use TightenCo\Jigsaw\Scaffold\PresetScaffold;
use \Mockery;
use org\bovigo\vfs\vfsStream;

class InitCommandTest extends TestCase
{
    /**
     * @test
     */
    public function init_command_with_no_arguments_uses_basic_scaffold_for_site()
    {
        $basic_scaffold = Mockery::mock(BasicScaffold::class);
        $basic_scaffold->shouldReceive('init');
        $this->app->instance(BasicScaffold::class, $basic_scaffold);

        $console = new CommandTester($this->app->make(InitCommand::class));
        $console->execute([]);

        $this->assertEquals('', $console->getInput()->getArgument('preset'));
        $this->assertContains('initialized', $console->getDisplay());
    }

    /**
     * @test
     */
    public function init_command_with_argument_uses_preset_scaffold_for_site()
    {
        $preset_scaffold = Mockery::mock(PresetScaffold::class);
        $preset_scaffold->shouldReceive('init')->with('blog');
        $this->app->instance(PresetScaffold::class, $preset_scaffold);

        $console = new CommandTester($this->app->make(InitCommand::class));
        $console->execute(['preset' => 'blog']);

        $this->assertEquals('blog', $console->getInput()->getArgument('preset'));
        $this->assertContains('initialized', $console->getDisplay());
    }

    /**
     * @test
     */
    public function init_command_displays_error_if_preset_name_is_invalid()
    {
        $console = new CommandTester($this->app->make(InitCommand::class));
        $console->execute(['preset' => 'invalid']);

        $this->assertContains("'invalid' is not a valid package name.", $console->getDisplay());
    }

    /**
     * @test
     */
    public function init_command_displays_error_if_preset_package_does_not_exist()
    {
        $console = new CommandTester($this->app->make(InitCommand::class));
        $console->execute(['preset' => 'invalid/package']);

        $this->assertContains("The package 'invalid/package' could not be found.", $console->getDisplay());
    }

    /**
     * @test
     */
    public function init_command_displays_warning_if_source_directory_exists()
    {
        $basic_scaffold = Mockery::mock(BasicScaffold::class);
        $basic_scaffold->shouldReceive('init');
        $basic_scaffold->shouldNotReceive('build');
        $this->app->instance(BasicScaffold::class, $basic_scaffold);

        $vfs = vfsStream::setup('virtual', null, ['source' => []]);
        $command = $this->app->make(InitCommand::class);
        $command->setApplication(new Application());
        $command->setBase($vfs->url());

        $console = new CommandTester($command);
        $console->setInputs(['c']);
        $console->execute([]);

        $this->assertContains("It looks like you've already run 'jigsaw init' on this project", $console->getDisplay());
    }

    /**
     * @test
     */
    public function init_command_displays_warning_if_config_dot_php_exists()
    {
        $basic_scaffold = Mockery::mock(BasicScaffold::class);
        $basic_scaffold->shouldReceive('init');
        $basic_scaffold->shouldNotReceive('build');
        $this->app->instance(BasicScaffold::class, $basic_scaffold);

        $vfs = vfsStream::setup('virtual', null, ['config.php' => '']);
        $command = $this->app->make(InitCommand::class);
        $command->setApplication(new Application());
        $command->setBase($vfs->url());

        $console = new CommandTester($command);
        $console->setInputs(['c']);
        $console->execute([]);

        $this->assertContains("It looks like you've already run 'jigsaw init' on this project", $console->getDisplay());
    }

    /**
     * @test
     */
    public function will_not_build_scaffold_if_site_already_initialized_and_user_chooses_cancel()
    {
        $basic_scaffold = Mockery::mock(BasicScaffold::class);
        $basic_scaffold->shouldReceive('init');
        $this->app->instance(BasicScaffold::class, $basic_scaffold);

        $vfs = vfsStream::setup('virtual', null, ['config.php' => '']);
        $command = $this->app->make(InitCommand::class);
        $command->setApplication(new Application());
        $command->setBase($vfs->url());

        $console = new CommandTester($command);
        $console->setInputs(['c']);
        $console->execute([]);

        $this->assertNotContains('initialized', $console->getDisplay());
    }

    /**
     * @test
     */
    public function will_build_scaffold_if_site_already_initialized_and_user_chooses_archive()
    {
        $basic_scaffold = Mockery::mock(BasicScaffold::class);
        $basic_scaffold->shouldReceive('init');
        $this->app->instance(BasicScaffold::class, $basic_scaffold);

        $vfs = vfsStream::setup('virtual', null, ['config.php' => '']);
        $command = $this->app->make(InitCommand::class);
        $command->setApplication(new Application());
        $command->setBase($vfs->url());

        $console = new CommandTester($command);
        $console->setInputs(['a']);
        $console->execute([]);

        $this->assertContains('initialized', $console->getDisplay());
    }

    /**
     * @test
     */
    public function will_delete_existing_site_if_user_chooses_delete_option()
    {
        $basic_scaffold = Mockery::mock(BasicScaffold::class);
        $basic_scaffold->shouldReceive('init');
        $this->app->instance(BasicScaffold::class, $basic_scaffold);

        $vfs = vfsStream::setup('virtual', null, ['config.php' => '']);
        $command = $this->app->make(InitCommand::class);
        $command->setApplication(new Application());
        $command->setBase($vfs->url());

        $console = new CommandTester($command);
        $console->setInputs(['d']);
        $console->setInputs(['y']);
        $console->execute([]);

        $this->assertContains('initialized', $console->getDisplay());
    }

    /**
     * @test
     */
    public function will_build_scaffold_if_site_already_initialized_and_user_chooses_delete()
    {
        $basic_scaffold = Mockery::mock(BasicScaffold::class);
        $basic_scaffold->shouldReceive('init');
        $this->app->instance(BasicScaffold::class, $basic_scaffold);

        $vfs = vfsStream::setup('virtual', null, ['config.php' => '']);
        $command = $this->app->make(InitCommand::class);
        $command->setApplication(new Application());
        $command->setBase($vfs->url());

        $console = new CommandTester($command);
        $console->setInputs(['d']);
        $console->setInputs(['y']);
        $console->execute([]);

        $this->assertContains('initialized', $console->getDisplay());
    }
}
