<?php

namespace Tests;

use Illuminate\Support\Arr;
use TightenCo\Jigsaw\File\ConfigFile;

class ConfigVariableTest extends TestCase
{
    /**
     * @test
     */
    public function config_variables_are_replaced_with_values_in_blade_templates()
    {
        $config = collect(['variable' => 'value']);
        $files = $this->setupSource([
            'variable-test.blade.php' => '<div>{!! $page->variable !!}</div>',
        ]);

        $this->buildSite($files, $config);

        $this->assertEquals(
            '<div>value</div>',
            $files->getChild('build/variable-test.html')->getContent()
        );
    }

    /**
     * @test
     */
    public function config_variables_are_loaded_from_dotenv_if_present()
    {
        $config = (new ConfigFile($this->app['cwd'].'/tests/config.php'))->config;

        $this->assertTrue($config['envVariable']);
    }

    /**
     * @test
     */
    public function config_variables_are_overridden_by_local_variables_in_blade_templates()
    {
        $config = collect(['variable' => 'config']);
        $yaml_header = implode("\n", ['---', 'variable: local', '---']);
        $files = $this->setupSource([
            'variable-test.blade.php' => $yaml_header . '<div>{!! $page->variable !!}</div>',
        ]);

        $this->buildSite($files, $config);

        $this->assertEquals(
            '<div>local</div>',
            $files->getChild('build/variable-test.html')->getContent()
        );
    }

    /**
     * @test
     */
    public function config_variables_are_merged_recursively()
    {
        $config = (new ConfigFile($this->app['cwd'] . '/tests/config.php'))
            ->config;
        $configToMerge = [
            'collections' => [
                'posts' => [
                    'sort' => 'date',
                    'isSelected' => 'foobar'
                ]
            ]
        ];

        $this->assertEquals(
            'Default Author',
            Arr::get($config, 'collections.posts.author')
        );
        $this->assertNotEquals(
            'foobar',
            Arr::get($config, 'collections.posts.isSelected')
        );
        $this->assertNull(Arr::get($config, 'collections.posts.sort'));

        $config = ConfigFile::mergeConfigs($config, $configToMerge);

        $this->assertEquals(
            'Default Author',
            Arr::get($config, 'collections.posts.author'),
            'collections.posts.author was merged but should NOT have been'
        );
        $this->assertEquals(
            'foobar',
            Arr::get($config, 'collections.posts.isSelected'),
            'collections.posts.isSelected was NOT merged but should have been'
        );
        $this->assertEquals(
            'date',
            Arr::get($config, 'collections.posts.sort'),
            'collections.posts.sort was NOT merged but should have been'
        );
    }
}
