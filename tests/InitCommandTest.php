<?php

namespace Tests;

use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use TightenCo\Jigsaw\Console\InitCommand;
use TightenCo\Jigsaw\Scaffold\BasicScaffoldBuilder;
use TightenCo\Jigsaw\Scaffold\PresetScaffoldBuilder;
use \Mockery;
use org\bovigo\vfs\vfsStream;

class InitCommandTest extends TestCase
{
    /**
     * @test
     */
    public function init_command_with_no_arguments_uses_basic_scaffold_for_site()
    {
        $basic_scaffold = Mockery::spy(BasicScaffoldBuilder::class);
        $basic_scaffold->shouldReceive('setBase')->andReturn($basic_scaffold);
        $this->app->instance(BasicScaffoldBuilder::class, $basic_scaffold);

        $vfs = vfsStream::setup('virtual', null, []);
        $command = $this->app->make(InitCommand::class);
        $command->setApplication(new Application());
        $command->setBase($vfs->url());

        $console = new CommandTester($command);
        $console->execute([]);

        $this->assertEquals('', $console->getInput()->getArgument('preset'));
        $this->assertContains('initialized', $console->getDisplay());
    }

    /**
     * @test
     */
    public function init_command_with_argument_uses_preset_scaffold_for_site()
    {
        $preset_scaffold = Mockery::spy(PresetScaffoldBuilder::class);
        $preset_scaffold->shouldReceive('setBase')->andReturn($preset_scaffold);
        $this->app->instance(PresetScaffoldBuilder::class, $preset_scaffold);

        $vfs = vfsStream::setup('virtual', null, []);
        $command = $this->app->make(InitCommand::class);
        $command->setApplication(new Application());
        $command->setBase($vfs->url());

        $console = new CommandTester($command);
        $console->execute(['preset' => 'blog']);

        $preset_scaffold->shouldHaveReceived('init')->with('blog');
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

        $this->assertContains("The package 'package' could not be found.", $console->getDisplay());
    }

    /**
     * @test
     */
    public function init_command_displays_warning_if_source_directory_exists()
    {
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
        $basic_scaffold = Mockery::spy(BasicScaffoldBuilder::class);
        $basic_scaffold->shouldReceive('setBase')->andReturn($basic_scaffold);
        $this->app->instance(BasicScaffoldBuilder::class, $basic_scaffold);

        $vfs = vfsStream::setup('virtual', null, ['config.php' => '']);
        $command = $this->app->make(InitCommand::class);
        $command->setApplication(new Application());
        $command->setBase($vfs->url());

        $console = new CommandTester($command);
        $console->setInputs(['c']);
        $console->execute([]);

        $basic_scaffold->shouldNotHaveReceived('archiveExistingSite');
        $basic_scaffold->shouldNotHaveReceived('build');
        $this->assertNotContains('initialized', $console->getDisplay());
    }

    /**
     * @test
     */
    public function will_archive_existing_site_if_user_chooses_archive_option()
    {
        $basic_scaffold = Mockery::spy(BasicScaffoldBuilder::class);
        $basic_scaffold->shouldReceive('setBase')->andReturn($basic_scaffold);
        $this->app->instance(BasicScaffoldBuilder::class, $basic_scaffold);

        $vfs = vfsStream::setup('virtual', null, ['config.php' => '']);
        $command = $this->app->make(InitCommand::class);
        $command->setApplication(new Application());
        $command->setBase($vfs->url());

        $console = new CommandTester($command);
        $console->setInputs(['a']);
        $console->execute([]);

        $basic_scaffold->shouldHaveReceived('archiveExistingSite');
        $this->assertContains('initialized', $console->getDisplay());
    }

    /**
     * @test
     */
    public function will_build_scaffold_if_site_already_initialized_and_user_chooses_archive()
    {
        $basic_scaffold = Mockery::spy(BasicScaffoldBuilder::class);
        $basic_scaffold->shouldReceive('setBase')->andReturn($basic_scaffold);
        $this->app->instance(BasicScaffoldBuilder::class, $basic_scaffold);

        $vfs = vfsStream::setup('virtual', null, ['config.php' => '']);
        $command = $this->app->make(InitCommand::class);
        $command->setApplication(new Application());
        $command->setBase($vfs->url());

        $console = new CommandTester($command);
        $console->setInputs(['a']);
        $console->execute([]);

        $basic_scaffold->shouldHaveReceived('build');
        $this->assertContains('initialized', $console->getDisplay());
    }

    /**
     * @test
     */
    public function will_delete_existing_site_if_user_chooses_delete_option()
    {
        $basic_scaffold = Mockery::spy(BasicScaffoldBuilder::class);
        $basic_scaffold->shouldReceive('setBase')->andReturn($basic_scaffold);
        $this->app->instance(BasicScaffoldBuilder::class, $basic_scaffold);

        $vfs = vfsStream::setup('virtual', null, ['config.php' => '']);
        $command = $this->app->make(InitCommand::class);
        $command->setApplication(new Application());
        $command->setBase($vfs->url());

        $console = new CommandTester($command);
        $console->setInputs(['d', 'y']);
        $console->execute([]);

        $basic_scaffold->shouldHaveReceived('deleteExistingSite');
        $this->assertContains('initialized', $console->getDisplay());
    }

    /**
     * @test
     */
    public function will_build_scaffold_if_site_already_initialized_and_user_chooses_delete()
    {
        $basic_scaffold = Mockery::spy(BasicScaffoldBuilder::class);
        $basic_scaffold->shouldReceive('setBase')->andReturn($basic_scaffold);
        $this->app->instance(BasicScaffoldBuilder::class, $basic_scaffold);

        $vfs = vfsStream::setup('virtual', null, ['config.php' => '']);
        $command = $this->app->make(InitCommand::class);
        $command->setApplication(new Application());
        $command->setBase($vfs->url());

        $console = new CommandTester($command);
        $console->setInputs(['d', 'y']);
        $console->execute([]);

        $basic_scaffold->shouldHaveReceived('build');
        $this->assertContains('initialized', $console->getDisplay());
    }
}
