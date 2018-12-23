<?php

namespace Tests;

use TightenCo\Jigsaw\File\Filesystem;
use TightenCo\Jigsaw\Scaffold\DefaultInstaller;
use TightenCo\Jigsaw\Scaffold\PresetPackage;
use TightenCo\Jigsaw\Scaffold\PresetScaffoldBuilder;
use TightenCo\Jigsaw\Scaffold\ProcessRunner;
use \Mockery;
use org\bovigo\vfs\vfsStream;

class DefaultScaffoldInstallerTest extends TestCase
{
    /**
     * @test
     */
    public function installer_installs_basic_scaffold_files()
    {
        $vfs = vfsStream::setup('virtual', null, []);
        $builder = new PresetScaffoldBuilder(new Filesystem, Mockery::mock(PresetPackage::class), new ProcessRunner);
        $builder->setBase($vfs->url());

        $this->assertCount(0, $vfs->getChildren());

        (new DefaultInstaller())->install($builder, ['commands' => []]);

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

        (new DefaultInstaller())->install($builder, [
            'delete' => 'config.php',
            'commands' => [],
        ]);

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

        (new DefaultInstaller())->install($builder, [
            'delete' => ['config.php', 'package.json'],
            'commands' => [],
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

        (new DefaultInstaller())->install($builder, [
            'delete' => ['source'],
            'commands' => [],
        ]);

        $this->assertNull($vfs->getChild('source'));
    }

    /**
     * @test
     */
    public function installer_copies_all_preset_files()
    {
        $vfs = vfsStream::setup('virtual', null, [
            'package' => [
                '.dotfile' => '',
                'preset-file.php' => '',
                'source' => [
                    'source-file.md' => '',
                ],
                'other' => [
                    'source-file.md' => '',
                    'nested' => [
                        'nested-file.md' => '',
                    ],
                ],
                'empty' => [],
            ],
        ]);
        $package = Mockery::mock(PresetPackage::class);
        $package->path = $vfs->url() . '/package';
        $builder = new PresetScaffoldBuilder(new Filesystem, $package, new ProcessRunner);
        $builder->setBase($vfs->url());

        (new DefaultInstaller())->install($builder, ['commands' => []]);

        $this->assertNotNull($vfs->getChild('.dotfile'));
        $this->assertNotNull($vfs->getChild('preset-file.php'));
        $this->assertNotNull($vfs->getChild('source/source-file.md'));
        $this->assertNotNull($vfs->getChild('other/nested/nested-file.md'));
        $this->assertNotNull($vfs->getChild('empty'));
    }

    /**
     * @test
     */
    public function installer_preserves_base_files_when_copying_preset_files()
    {
        $vfs = vfsStream::setup('virtual', null, [
            'package' => [
                'preset-file.php' => '',
            ],
        ]);
        $package = Mockery::mock(PresetPackage::class);
        $package->path = $vfs->url() . '/package';
        $builder = new PresetScaffoldBuilder(new Filesystem, $package, new ProcessRunner);
        $builder->setBase($vfs->url());

        (new DefaultInstaller())->install($builder, ['commands' => []]);

        $this->assertNotNull($vfs->getChild('config.php'));
    }

    /**
     * @test
     */
    public function installer_overwrites_base_files_of_same_name_when_copying_preset_files()
    {
        $vfs = vfsStream::setup('virtual', null, [
            'package' => [
                'config.php' => 'new config file from preset',
            ],
        ]);
        $package = Mockery::mock(PresetPackage::class);
        $package->path = $vfs->url() . '/package';
        $builder = new PresetScaffoldBuilder(new Filesystem, $package, new ProcessRunner);
        $builder->setBase($vfs->url());

        (new DefaultInstaller())->install($builder, ['commands' => []]);

        $this->assertEquals(
            'new config file from preset',
            $vfs->getChild('config.php')->getContent()
        );
    }

    /**
     * @test
     */
    public function installer_can_ignore_files_and_directories_from_preset_when_copying()
    {
        $vfs = vfsStream::setup('virtual', null, [
            'package' => [
                'copy-this.php' => '',
                'ignore-this.php' => '',
                'copy-directory' => [
                    'copy-this.php' => '',
                ],
                'ignore-directory' => [
                    'ignore-this.php' => '',
                ],
            ],
        ]);
        $package = Mockery::mock(PresetPackage::class);
        $package->path = $vfs->url() . '/package';
        $builder = new PresetScaffoldBuilder(new Filesystem, $package, new ProcessRunner);
        $builder->setBase($vfs->url());

        (new DefaultInstaller())->install($builder, [
            'ignore' => [
                'ignore-this.php',
                'ignore-directory',
            ],
            'commands' => [],
        ]);

        $this->assertNotNull($vfs->getChild('copy-this.php'));
        $this->assertNull($vfs->getChild('ignore-this.php'));
        $this->assertNotNull($vfs->getChild('copy-directory'));
        $this->assertNull($vfs->getChild('ignore-directory'));
    }

    /**
     * @test
     */
    public function installer_can_ignore_files_and_directories_from_preset_using_wildcard_when_copying()
    {
        $vfs = vfsStream::setup('virtual', null, [
            'package' => [
                'copy-this.php' => '',
                'ignore-this.php' => '',
                'copy-directory' => [
                    'copy-this.php' => '',
                ],
                'ignore-directory' => [
                    'ignore-this.php' => '',
                ],
            ],
        ]);
        $package = Mockery::mock(PresetPackage::class);
        $package->path = $vfs->url() . '/package';
        $builder = new PresetScaffoldBuilder(new Filesystem, $package, new ProcessRunner);
        $builder->setBase($vfs->url());

        (new DefaultInstaller())->install($builder, [
            'ignore' => [
                'ignore*',
            ],
            'commands' => [],
        ]);

        $this->assertNotNull($vfs->getChild('copy-this.php'));
        $this->assertNull($vfs->getChild('ignore-this.php'));
        $this->assertNotNull($vfs->getChild('copy-directory'));
        $this->assertNull($vfs->getChild('ignore-directory'));
    }

    /**
     * @test
     */
    public function installer_ignores_node_modules_directory_from_preset_when_copying()
    {
        $vfs = vfsStream::setup('virtual', null, [
            'package' => [
                'node_modules' => [],
            ],
        ]);
        $package = Mockery::mock(PresetPackage::class);
        $package->path = $vfs->url() . '/package';
        $builder = new PresetScaffoldBuilder(new Filesystem, $package, new ProcessRunner);
        $builder->setBase($vfs->url());

        (new DefaultInstaller())->install($builder, ['commands' => []]);

        $this->assertNull($vfs->getChild('node_modules'));
    }

    /**
     * @test
     */
    public function installer_ignores_vendor_directory_from_preset_when_copying()
    {
        $vfs = vfsStream::setup('virtual', null, [
            'package' => [
                'vendor' => [],
            ],
        ]);
        $package = Mockery::mock(PresetPackage::class);
        $package->path = $vfs->url() . '/package';
        $builder = new PresetScaffoldBuilder(new Filesystem, $package, new ProcessRunner);
        $builder->setBase($vfs->url());

        (new DefaultInstaller())->install($builder, ['commands' => []]);

        $this->assertNull($vfs->getChild('vendor'));
    }

    /**
     * @test
     */
    public function installer_ignores_init_file_from_preset_when_copying()
    {
        $vfs = vfsStream::setup('virtual', null, [
            'package' => [
                'init.php' => 'the init file',
            ],
        ]);
        $package = Mockery::mock(PresetPackage::class);
        $package->path = $vfs->url() . '/package';
        $builder = new PresetScaffoldBuilder(new Filesystem, $package, new ProcessRunner);
        $builder->setBase($vfs->url());

        (new DefaultInstaller())->install($builder, ['commands' => []]);

        $this->assertNull($vfs->getChild('init.php'));
    }

    /**
     * @test
     */
    public function installer_ignores_build_directories_from_preset_when_copying()
    {
        $vfs = vfsStream::setup('virtual', null, [
            'package' => [
                'build_local' => [],
                'build_production' => [],
            ],
        ]);
        $package = Mockery::mock(PresetPackage::class);
        $package->path = $vfs->url() . '/package';
        $builder = new PresetScaffoldBuilder(new Filesystem, $package, new ProcessRunner);
        $builder->setBase($vfs->url());

        (new DefaultInstaller())->install($builder, ['commands' => []]);

        $this->assertNull($vfs->getChild('build_local'));
        $this->assertNull($vfs->getChild('build_production'));
    }

    /**
     * @test
     * @doesNotPerformAssertions
     */
    public function installer_runs_default_commands_if_none_are_specified_in_init()
    {
        $builder = Mockery::spy(PresetScaffoldBuilder::class);
        $builder->shouldReceive('buildBasicScaffold')->andReturn($builder);
        $builder->shouldReceive('cacheComposerDotJson')->andReturn($builder);
        $builder->shouldReceive('deleteSiteFiles')->andReturn($builder);
        $builder->shouldReceive('copyPresetFiles')->andReturn($builder);
        $builder->shouldReceive('mergeComposerDotJson')->andReturn($builder);

        $installer = new DefaultInstaller();
        $installer->install($builder);

        $builder->shouldHaveReceived('runCommands')->with($installer::DEFAULT_COMMANDS);
    }

    /**
     * @test
     * @doesNotPerformAssertions
     */
    public function installer_runs_no_commands_if_empty_array_is_specified_in_init()
    {
        $builder = Mockery::spy(PresetScaffoldBuilder::class);
        $builder->shouldReceive('buildBasicScaffold')->andReturn($builder);
        $builder->shouldReceive('cacheComposerDotJson')->andReturn($builder);
        $builder->shouldReceive('deleteSiteFiles')->andReturn($builder);
        $builder->shouldReceive('copyPresetFiles')->andReturn($builder);
        $builder->shouldReceive('mergeComposerDotJson')->andReturn($builder);

        $installer = new DefaultInstaller();
        $installer->install($builder, ['commands' => []]);

        $builder->shouldHaveReceived('runCommands')->with([]);
    }

    /**
     * @test
     * @doesNotPerformAssertions
     */
    public function installer_runs_specified_commands_from_init()
    {
        $builder = Mockery::spy(PresetScaffoldBuilder::class);
        $builder->shouldReceive('buildBasicScaffold')->andReturn($builder);
        $builder->shouldReceive('cacheComposerDotJson')->andReturn($builder);
        $builder->shouldReceive('deleteSiteFiles')->andReturn($builder);
        $builder->shouldReceive('copyPresetFiles')->andReturn($builder);
        $builder->shouldReceive('mergeComposerDotJson')->andReturn($builder);

        $installer = new DefaultInstaller();
        $installer->install($builder, ['commands' => ['yarn']]);

        $builder->shouldHaveReceived('runCommands')->with(['yarn']);
    }

    /**
     * @test
     */
    public function composer_json_is_restored_if_deleted_after_preset_is_installed()
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

        (new DefaultInstaller())->install($builder, ['commands' => [], 'delete' => 'composer.json']);

        $this->assertNotNull($vfs->getChild('composer.json'));
        $this->assertEquals($old_composer, json_decode($vfs->getChild('composer.json')->getContent(), true));
    }

    /**
     * @test
     */
    public function original_composer_json_is_merged_with_composer_json_installed_by_preset()
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
                'composer.json' => json_encode([
                    'repository' => 'setting',
                    'require' => [
                        'other/dependency' => '5.1',
                    ],
                ]),
            ],
        ]);
        $package = Mockery::mock(PresetPackage::class);
        $package->path = $vfs->url() . '/package';
        $builder = new PresetScaffoldBuilder(new Filesystem, $package, new ProcessRunner);
        $builder->setBase($vfs->url());

        (new DefaultInstaller())->install($builder, ['commands' => [], 'delete' => 'composer.json']);

        $new_composer = json_decode($vfs->getChild('composer.json')->getContent(), true);

        $this->assertEquals('^1.2', array_get($new_composer, 'require.tightenco/jigsaw'));
        $this->assertEquals('5.1', array_get($new_composer, 'require.other/dependency'));
        $this->assertEquals('setting', array_get($new_composer, 'repository'));
    }

    /**
     * @test
     */
    public function version_constraints_from_original_composer_json_take_precedence_in_merged_composer_json()
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
                'composer.json' => json_encode([
                    'require' => [
                        'tightenco/jigsaw' => '1.0',
                        'new-package/test' => '3.0'
                    ],
                ]),
            ],
        ]);
        $package = Mockery::mock(PresetPackage::class);
        $package->path = $vfs->url() . '/package';
        $builder = new PresetScaffoldBuilder(new Filesystem, $package, new ProcessRunner);
        $builder->setBase($vfs->url());

        (new DefaultInstaller())->install($builder, ['commands' => [], 'delete' => 'composer.json']);

        $new_composer = json_decode($vfs->getChild('composer.json')->getContent(), true);

        $this->assertEquals('^1.2', array_get($new_composer, 'require.tightenco/jigsaw'));
        $this->assertEquals('1.0', array_get($new_composer, 'require.test/preset'));
        $this->assertEquals('3.0', array_get($new_composer, 'require.new-package/test'));
    }

    /**
     * @test
     */
    public function empty_composer_json_is_created_if_it_was_not_present_before_preset_was_installed()
    {
        $vfs = vfsStream::setup('virtual', null, [
            'package' => [
                'preset-file.php' => '',
            ],
        ]);
        $package = Mockery::mock(PresetPackage::class);
        $package->path = $vfs->url() . '/package';
        $builder = new PresetScaffoldBuilder(new Filesystem, $package, new ProcessRunner);
        $builder->setBase($vfs->url());

        (new DefaultInstaller())->install($builder, ['commands' => [], 'delete' => 'composer.json']);

        $this->assertEquals(
            [
                'require' => [],
            ],
            json_decode($vfs->getChild('composer.json')->getContent(), true)
        );
    }
}
