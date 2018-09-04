<?php

namespace Tests;

use TightenCo\Jigsaw\Scaffold\CustomInstaller;
use TightenCo\Jigsaw\Scaffold\CustomQueue;
use TightenCo\Jigsaw\Scaffold\DefaultInstaller;
use TightenCo\Jigsaw\Scaffold\DefaultQueue;
use TightenCo\Jigsaw\Scaffold\PresetPackage;
use TightenCo\Jigsaw\Scaffold\PresetScaffoldBuilder;
use TightenCo\Jigsaw\Scaffold\ProcessRunner;
use \Mockery;
use org\bovigo\vfs\vfsStream;

class PresetScaffoldBuilderTest extends TestCase
{
    /**
     * @test
     */
    public function named_preset_resolves_to_predefined_package_path()
    {
        $preset = $this->app->make(PresetScaffoldBuilder::class);
        $vfs = vfsStream::setup('virtual', null, [
            'vendor' => [
                'tightenco' => [
                    'jigsaw-preset-blog' => [
                        'init.php' => '',
                    ],
                ],
            ],
        ]);
        $preset->base = $vfs->url();

        $preset->init('blog');

        $this->assertEquals(
            $vfs->url() . '/vendor/tightenco/jigsaw-preset-blog',
            $preset->package->path
        );
    }

    /**
     * @test
     */
    public function named_preset_resolves_to_vendor_package_path_if_not_predefined()
    {
        $preset = $this->app->make(PresetScaffoldBuilder::class);
        $vfs = vfsStream::setup('virtual', null, [
            'vendor' => [
                'test' => [
                    'package' => [
                        'init.php' => '',
                    ],
                ],
            ],
        ]);
        $preset->base = $vfs->url();

        $preset->init('test/package');

        $this->assertEquals($vfs->url() . '/vendor/' . 'test/package', $preset->package->path);
    }

    /**
     * @test
     * @doesNotPerformAssertions
     */
    public function package_is_loaded_via_composer_if_not_found_locally()
    {
        $process = Mockery::spy(ProcessRunner::class);
        $this->app->instance(PresetPackage::class, new PresetPackage(new DefaultInstaller, new CustomInstaller, $process));
        $preset = $this->app->make(PresetScaffoldBuilder::class);
        $vfs = vfsStream::setup('virtual', null, ['vendor' => ['test' => ['package' => []]]]);
        $preset->base = $vfs->url();

        $preset->init('test/other-package');

        $process->shouldHaveReceived('run')->with('composer require test/other-package');
    }

    /**
     * @test
     */
    public function exception_is_thrown_if_package_is_missing_a_slash()
    {
        $preset = $this->app->make(PresetScaffoldBuilder::class);
        $vfs = vfsStream::setup('virtual', null, ['vendor' => ['test' => ['package' => []]]]);
        $preset->base = $vfs->url();

        try {
            $preset->init('test');
            $this->fail('Exception not thrown');
        } catch (\Exception $e) {
            $this->assertContains(
                "'test' is not a valid package name.",
                $e->getMessage()
            );
        }
    }

    /**
     * @test
     */
    public function exception_is_thrown_if_package_init_file_contains_errors()
    {
        $preset = $this->app->make(PresetScaffoldBuilder::class);
        $initFile = '<?php contains-an-error;';
        $vfs = vfsStream::setup('virtual', null, [
            'vendor' => [
                'test' => [
                    'preset' => [
                        'init.php' => $initFile,
                    ],
                ],
            ],
        ]);
        $preset->base = $vfs->url();

        try {
            $preset->init('test/preset');
            $preset->build();

            $this->fail('Exception not thrown');
        } catch (\Exception $e) {
            $this->assertContains("contains errors", $e->getMessage());
        }
    }

    /**
     * @test
     * @doesNotPerformAssertions
     */
    public function init_file_of_array_type_is_loaded()
    {
        $default_installer = Mockery::spy(DefaultInstaller::class);
        $this->app->instance(DefaultInstaller::class, $default_installer);
        $preset = $this->app->make(PresetScaffoldBuilder::class);

        $initFile = '<?php return [
            "delete" => ["test.json"],
        ];';
        $vfs = vfsStream::setup('virtual', null, [
            'vendor' => [
                'test' => [
                    'preset' => [
                        'init.php' => $initFile,
                    ],
                ],
            ],
        ]);
        $preset->base = $vfs->url();

        $preset->init('test/preset');
        $preset->build();

        $default_installer->shouldHaveReceived('install')
            ->with($preset, ['delete' => ['test.json']]);
    }

    /**
     * @test
     * @doesNotPerformAssertions
     */
    public function init_file_of_php_type_is_loaded()
    {
        $custom_installer = Mockery::spy(CustomInstaller::class);
        $this->app->instance(CustomInstaller::class, $custom_installer);
        $preset = $this->app->make(PresetScaffoldBuilder::class);

        $custom_installer->shouldReceive('install')
            ->with($preset)->andReturn($custom_installer);

        $initFile = '<?php $init->copy("test");';
        $vfs = vfsStream::setup('virtual', null, [
            'vendor' => [
                'test' => [
                    'preset' => [
                        'init.php' => $initFile,
                    ],
                ],
            ],
        ]);
        $preset->base = $vfs->url();

        $preset->init('test/preset');
        $preset->build();

        $custom_installer->shouldHaveReceived('copy')->with('test');
    }

    /**
     * @test
     * @doesNotPerformAssertions
     */
    public function init_file_is_optional()
    {
        $default_installer = Mockery::spy(DefaultInstaller::class);
        $this->app->instance(DefaultInstaller::class, $default_installer);
        $preset = $this->app->make(PresetScaffoldBuilder::class);

        $vfs = vfsStream::setup('virtual', null, [
            'vendor' => [
                'test' => [
                    'preset' => [],
                ],
            ],
        ]);
        $preset->base = $vfs->url();

        $preset->init('test/preset');
        $preset->build();

        $default_installer->shouldHaveReceived('install')->with($preset, []);
    }

    /**
     * @test
     */
    public function preset_package_dependency_is_restored_to_fresh_composer_dot_json_when_archiving_site()
    {
        $old_composer = [
            'require' => [
                'tightenco/jigsaw' => '^1.2',
                'test/preset' => '1.0',
            ],
        ];
        $existing_site = [
            'composer.json' => json_encode($old_composer),
            'vendor' => [
                'test' => [
                    'preset' => [],
                ],
            ],
        ];
        $vfs = vfsStream::setup('virtual', null, $existing_site);
        $preset = $this->app->make(PresetScaffoldBuilder::class)
            ->setBase($vfs->url())
            ->init('test/preset');

        $preset->archiveExistingSite();

        $this->assertEquals($old_composer, json_decode($vfs->getChild('composer.json')->getContent(), true));
    }

    /**
     * @test
     */
    public function preset_package_dependency_is_restored_to_fresh_composer_dot_json_when_deleting_site()
    {
        $old_composer = [
            'require' => [
                'tightenco/jigsaw' => '^1.2',
                'test/preset' => '1.0',
            ],
        ];
        $existing_site = [
            'composer.json' => json_encode($old_composer),
            'vendor' => [
                'test' => [
                    'preset' => [],
                ],
            ],
        ];
        $vfs = vfsStream::setup('virtual', null, $existing_site);
        $preset = $this->app->make(PresetScaffoldBuilder::class)
            ->setBase($vfs->url())
            ->init('test/preset');

        $preset->deleteExistingSite();

        $this->assertEquals($old_composer, json_decode($vfs->getChild('composer.json')->getContent(), true));
    }
}
