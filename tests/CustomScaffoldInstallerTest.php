<?php

namespace Tests;

use TightenCo\Jigsaw\Console\ConsoleSession;
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
    public function installer_can_copy_files_using_a_wildcard()
    {
        $vfs = vfsStream::setup('virtual', null, [
            'package' => [
                '.dotfile' => '',
                'preset-file-1.php' => '',
                'preset-file-2.php' => '',
            ],
        ]);
        $package = Mockery::mock(PresetPackage::class);
        $package->path = $vfs->url() . '/package';
        $builder = new PresetScaffoldBuilder(new Filesystem, $package, new ProcessRunner);
        $builder->setBase($vfs->url());

        (new CustomInstaller())->install($builder)
            ->setup()
            ->copy([
                'preset-file-*.php',
            ]);

        $this->assertNotNull($vfs->getChild('preset-file-1.php'));
        $this->assertNotNull($vfs->getChild('preset-file-1.php'));
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
    public function installer_copies_from_specified_directory_to_root_if_from_is_specified()
    {
        $vfs = vfsStream::setup('virtual', null, [
            'package' => [
                'config.php' => 'config root',
                'themes' => [
                    'directory-1' => [
                        'config.php' => 'config 1',
                    ],
                    'directory-2' => [
                        'config.php' => 'config 2',
                    ],
                ],
            ],
        ]);
        $package = Mockery::mock(PresetPackage::class);
        $package->path = $vfs->url() . '/package';
        $builder = new PresetScaffoldBuilder(new Filesystem, $package, new ProcessRunner);
        $builder->setBase($vfs->url());

        (new CustomInstaller())->install($builder)
            ->setup()
            ->from('themes/directory-2')
            ->copy();

        $this->assertNotNull($vfs->getChild('config.php'));
        $this->assertEquals('config 2', $vfs->getChild('config.php')->getContent());
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

    /**
     * @test
     */
    public function original_composer_json_is_not_deleted()
    {
        $old_composer = [
            'require' => [
                'tightenco/jigsaw' => '^1.2',
                'test/preset' => '1.0',
            ],
        ];
        $vfs = vfsStream::setup('virtual', null, [
            'composer.json' => json_encode($old_composer),
            'package' => [
                'preset-file.php' => '',
            ],
        ]);
        $package = Mockery::mock(PresetPackage::class);
        $package->path = $vfs->url() . '/package';
        $builder = new PresetScaffoldBuilder(new Filesystem, $package, new ProcessRunner);
        $builder->setBase($vfs->url());

        (new CustomInstaller())->install($builder)
            ->setup()
            ->delete('composer.json');

        $this->assertNotNull($vfs->getChild('composer.json'));
        $this->assertEquals(
            $old_composer,
            json_decode($vfs->getChild('composer.json')->getContent(), true)
        );
    }

    /**
     * @test
     */
    public function original_composer_json_is_merged_with_new_composer_json_after_copy()
    {
        $old_composer = [
            'require' => [
                'tightenco/jigsaw' => '^1.2',
                'test/preset' => '1.0',
            ],
        ];
        $vfs = vfsStream::setup('virtual', null, [
            'composer.json' => json_encode($old_composer),
            'package' => [
                'composer.json' => json_encode([
                    'require' => [
                        'other/package' => '2.0',
                    ],
                ]),
            ],
        ]);
        $package = Mockery::mock(PresetPackage::class);
        $package->path = $vfs->url() . '/package';
        $builder = new PresetScaffoldBuilder(new Filesystem, $package, new ProcessRunner);
        $builder->setBase($vfs->url());

        (new CustomInstaller())->install($builder)
            ->setup()
            ->copy();

        $this->assertEquals(
            [
                'require' => [
                    'tightenco/jigsaw' => '^1.2',
                    'test/preset' => '1.0',
                    'other/package' => '2.0'
                ],
            ],
            json_decode($vfs->getChild('composer.json')->getContent(), true)
        );
    }

    /**
     * @test
     */
    public function composer_json_files_are_merged_when_copying_multiple_times()
    {
        $old_composer = [
            'require' => [
                'tightenco/jigsaw' => '^1.2',
                'test/preset' => '1.0',
            ],
        ];
        $vfs = vfsStream::setup('virtual', null, [
            'composer.json' => json_encode($old_composer),
            'package' => [
                'composer.json' => json_encode([
                    'require' => [
                        'other/package' => '2.0',
                    ],
                ]),
                'theme' => [
                    'composer.json' => json_encode([
                        'require' => [
                            'another/package' => '3.0',
                        ],
                    ]),
                ],
            ],
        ]);
        $package = Mockery::mock(PresetPackage::class);
        $package->path = $vfs->url() . '/package';
        $builder = new PresetScaffoldBuilder(new Filesystem, $package, new ProcessRunner);
        $builder->setBase($vfs->url());

        (new CustomInstaller())->install($builder)
            ->setup()
            ->copy()
            ->from('theme')
            ->copy();

        $this->assertEquals(
            [
                'require' => [
                    'tightenco/jigsaw' => '^1.2',
                    'test/preset' => '1.0',
                    'other/package' => '2.0',
                    'another/package' => '3.0'
                ],
            ],
            json_decode($vfs->getChild('composer.json')->getContent(), true)
        );
    }

    /**
     * @test
     */
    public function empty_composer_json_is_created_if_it_was_not_present_before_preset_was_installed()
    {
        $vfs = vfsStream::setup('virtual', null, [
            'package' => [
                'package-file.php' => '',
            ],
        ]);
        $package = Mockery::mock(PresetPackage::class);
        $package->path = $vfs->url() . '/package';
        $builder = new PresetScaffoldBuilder(new Filesystem, $package, new ProcessRunner);
        $builder->setBase($vfs->url());

        (new CustomInstaller())->install($builder)
            ->setup()
            ->copy();

        $this->assertEquals(
            [
                'require' => [],
            ],
            json_decode($vfs->getChild('composer.json')->getContent(), true)
        );
    }

    /**
     * @test
     * @doesNotPerformAssertions
     */
    public function installer_can_ask_for_user_input()
    {
        $console = Mockery::spy(ConsoleSession::class);
        $builder = Mockery::spy(PresetScaffoldBuilder::class);

        (new CustomInstaller())->setConsole($console)
            ->install($builder)
            ->setup()
            ->ask('What is your name?');

        $console->shouldHaveReceived('ask')
            ->with('What is your name?', null, null, null);
    }

    /**
     * @test
     * @doesNotPerformAssertions
     */
    public function installer_can_ask_for_user_input_with_choices()
    {
        $console = Mockery::spy(ConsoleSession::class);
        $builder = Mockery::spy(PresetScaffoldBuilder::class);

        (new CustomInstaller())->setConsole($console)
            ->install($builder)
            ->setup()
            ->ask(
                'What theme would you like to use?',
                ['l' => 'light', 'd' => 'dark'],
                $default = 'l'
            );

        $console->shouldHaveReceived('ask')
            ->with(
                'What theme would you like to use?',
                ['l' => 'light', 'd' => 'dark'],
                'l',
                null
            );
    }

    /**
     * @test
     * @doesNotPerformAssertions
     */
    public function installer_can_ask_for_user_confirmation()
    {
        $console = Mockery::spy(ConsoleSession::class);
        $builder = Mockery::spy(PresetScaffoldBuilder::class);

        (new CustomInstaller())->setConsole($console)
            ->install($builder)
            ->setup()
            ->confirm('Continue?');

        $console->shouldHaveReceived('confirm')
            ->with('Continue?', null);
    }

    /**
     * @test
     * @doesNotPerformAssertions
     */
    public function installer_runs_specified_commands_from_init()
    {
        $package = Mockery::mock(PresetPackage::class);
        $builder = Mockery::spy(PresetScaffoldBuilder::class);

        (new CustomInstaller())->setConsole(null)
            ->install($builder)
            ->setup()
            ->run('yarn');

        $builder->shouldHaveReceived('runCommands')->with('yarn');
    }
}
