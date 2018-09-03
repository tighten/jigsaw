<?php

namespace Tests;

use TightenCo\Jigsaw\Scaffold\CustomInstaller;
use TightenCo\Jigsaw\Scaffold\CustomQueue;
use TightenCo\Jigsaw\Scaffold\DefaultInstaller;
use TightenCo\Jigsaw\Scaffold\DefaultQueue;
use TightenCo\Jigsaw\Scaffold\PresetScaffoldBuilder;
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
     */
    public function exception_is_thrown_if_package_can_not_be_found()
    {
        $preset = $this->app->make(PresetScaffoldBuilder::class);
        $vfs = vfsStream::setup('virtual', null, ['vendor' => ['test' => ['package' => []]]]);
        $preset->base = $vfs->url();

        try {
            $preset->init('test/other-package');
            $this->fail('Exception not thrown');
        } catch (\Exception $e) {
            $this->assertContains(
                "The package 'other-package' could not be found.",
                $e->getMessage()
            );
        }
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
}
