<?php

namespace Tests;

use TightenCo\Jigsaw\File\Filesystem;
use TightenCo\Jigsaw\Scaffold\CustomInstaller;
use TightenCo\Jigsaw\Scaffold\PresetPackage;
use TightenCo\Jigsaw\Scaffold\PresetScaffoldBuilder;
use TightenCo\Jigsaw\Scaffold\ProcessRunner;
use \Mockery;
use org\bovigo\vfs\vfsStream;

class CustomScaffoldInstallerTest extends TestCase
{
    /**
     * @test
     */
    public function custom_installer_installs_basic_scaffold_files()
    {
        $vfs = vfsStream::setup('virtual', null, []);
        $builder = new PresetScaffoldBuilder(new Filesystem, Mockery::mock(PresetPackage::class), new ProcessRunner);
        $builder->setBase($vfs->url());

        $this->assertCount(0, $vfs->getChildren());

        (new CustomInstaller())->install($builder)
            ->setup();

        $this->assertNotNull($vfs->getChild('source'));
        $this->assertNotNull($vfs->getChild('tasks'));
        $this->assertNotNull($vfs->getChild('package.json'));
        $this->assertNotNull($vfs->getChild('webpack.mix.js'));
        $this->assertNotNull($vfs->getChild('config.php'));
    }

    /**
     * @test
     */
    public function installer_deletes_single_base_file_specified_in_delete_array()
    {
        $vfs = vfsStream::setup('virtual', null, []);
        $builder = new PresetScaffoldBuilder(new Filesystem, Mockery::mock(PresetPackage::class), new ProcessRunner);
        $builder->setBase($vfs->url());

        (new CustomInstaller())->install($builder)
            ->setup()
            ->delete('config.php');

        $this->assertNull($vfs->getChild('config.php'));
        $this->assertNotNull($vfs->getChild('source'));
    }

    /**
     * @test
     */
    public function installer_deletes_multiple_base_files_specified_in_delete_array()
    {
        $vfs = vfsStream::setup('virtual', null, []);
        $builder = new PresetScaffoldBuilder(new Filesystem, Mockery::mock(PresetPackage::class), new ProcessRunner);
        $builder->setBase($vfs->url());

        (new CustomInstaller())->install($builder)
            ->setup()
            ->delete([
                'config.php',
                'package.json'
            ]);

        $this->assertNull($vfs->getChild('config.php'));
        $this->assertNull($vfs->getChild('package.json'));
        $this->assertNotNull($vfs->getChild('source'));
    }

    /**
     * @test
     */
    public function installer_deletes_base_directories_specified_in_delete_array()
    {
        $vfs = vfsStream::setup('virtual', null, []);
        $builder = new PresetScaffoldBuilder(new Filesystem, Mockery::mock(PresetPackage::class), new ProcessRunner);
        $builder->setBase($vfs->url());

        (new CustomInstaller())->install($builder)
            ->setup()
            ->delete([
                'source',
            ]);

        $this->assertNull($vfs->getChild('source'));
    }

    /**
     * @test
     */
    public function installer_copies_all_preset_files_if_copy_has_no_parameter()
    {
        $vfs = vfsStream::setup('virtual', null, [
            'package' => [
                '.dotfile' => '',
                'preset-file.php' => '',
                'source' => [
                    'source-file.md' => '',
                ],
            ],
        ]);
        $package = Mockery::mock(PresetPackage::class);
        $package->path = $vfs->url() . '/package';
        $builder = new PresetScaffoldBuilder(new Filesystem, $package, new ProcessRunner);
        $builder->setBase($vfs->url());

        (new CustomInstaller())->install($builder)
            ->setup()
            ->copy();

        $this->assertNotNull($vfs->getChild('.dotfile'));
        $this->assertNotNull($vfs->getChild('preset-file.php'));
        $this->assertNotNull($vfs->getChild('source/source-file.md'));
    }

    /**
     * @test
     */
    public function installer_copies_individual_preset_file_if_copy_parameter_is_string()
    {
        $vfs = vfsStream::setup('virtual', null, [
            'package' => [
                '.dotfile' => '',
                'preset-file.php' => '',
                'source' => [
                    'source-file.md' => '',
                ],
            ],
        ]);
        $package = Mockery::mock(PresetPackage::class);
        $package->path = $vfs->url() . '/package';
        $builder = new PresetScaffoldBuilder(new Filesystem, $package, new ProcessRunner);
        $builder->setBase($vfs->url());

        (new CustomInstaller())->install($builder)
            ->setup()
            ->copy('preset-file.php');

        $this->assertNotNull($vfs->getChild('preset-file.php'));
        $this->assertNull($vfs->getChild('.dotfile'));
        $this->assertNull($vfs->getChild('source/source-file.md'));
    }

    /**
     * @test
     */
    public function installer_copies_multiple_preset_files_if_copy_parameter_is_array()
    {
        $vfs = vfsStream::setup('virtual', null, [
            'package' => [
                '.dotfile' => '',
                'preset-file.php' => '',
                'source' => [
                    'source-file.md' => '',
                ],
            ],
        ]);
        $package = Mockery::mock(PresetPackage::class);
        $package->path = $vfs->url() . '/package';
        $builder = new PresetScaffoldBuilder(new Filesystem, $package, new ProcessRunner);
        $builder->setBase($vfs->url());

        (new CustomInstaller())->install($builder)
            ->setup()
            ->copy([
                'preset-file.php',
                'source/source-file.md',
            ]);

        $this->assertNotNull($vfs->getChild('preset-file.php'));
        $this->assertNotNull($vfs->getChild('source/source-file.md'));
        $this->assertNull($vfs->getChild('.dotfile'));
    }

    /**
     * @test
     */
    public function installer_can_call_copy_multiple_times()
    {
        $vfs = vfsStream::setup('virtual', null, [
            'package' => [
                '.dotfile' => '',
                'preset-file.php' => '',
                'source' => [
                    'source-file.md' => '',
                ],
            ],
        ]);
        $package = Mockery::mock(PresetPackage::class);
        $package->path = $vfs->url() . '/package';
        $builder = new PresetScaffoldBuilder(new Filesystem, $package, new ProcessRunner);
        $builder->setBase($vfs->url());

        (new CustomInstaller())->install($builder)
            ->setup()
            ->copy('.dotfile')
            ->copy('source');

        $this->assertNotNull($vfs->getChild('.dotfile'));
        $this->assertNotNull($vfs->getChild('source/source-file.md'));
        $this->assertNull($vfs->getChild('preset-file.php'));
    }

    /**
     * @test
     */
    public function installer_can_ignore_preset_files_when_copying()
    {
        $vfs = vfsStream::setup('virtual', null, [
            'package' => [
                '.dotfile' => '',
                'preset-file.php' => '',
                'source' => [
                    'source-file.md' => '',
                ],
            ],
        ]);
        $package = Mockery::mock(PresetPackage::class);
        $package->path = $vfs->url() . '/package';
        $builder = new PresetScaffoldBuilder(new Filesystem, $package, new ProcessRunner);
        $builder->setBase($vfs->url());

        (new CustomInstaller())->install($builder)
            ->setup()
            ->ignore('.dotfile')
            ->copy();

        $this->assertNotNull($vfs->getChild('preset-file.php'));
        $this->assertNotNull($vfs->getChild('source/source-file.md'));
        $this->assertNull($vfs->getChild('.dotfile'));
    }

    /**
     * @test
     */
    public function installer_can_call_ignore_multiple_times()
    {
        $vfs = vfsStream::setup('virtual', null, [
            'package' => [
                '.dotfile' => '',
                'preset-file.php' => '',
                'source' => [
                    'source-file.md' => '',
                ],
            ],
        ]);
        $package = Mockery::mock(PresetPackage::class);
        $package->path = $vfs->url() . '/package';
        $builder = new PresetScaffoldBuilder(new Filesystem, $package, new ProcessRunner);
        $builder->setBase($vfs->url());

        (new CustomInstaller())->install($builder)
            ->setup()
            ->ignore('.dotfile')
            ->ignore('preset-file.php')
            ->copy();

        $this->assertNotNull($vfs->getChild('source/source-file.md'));
        $this->assertNull($vfs->getChild('preset-file.php'));
        $this->assertNull($vfs->getChild('.dotfile'));
    }
}
