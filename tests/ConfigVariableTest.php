<?php

namespace Tests;

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
        putenv('JIGSAW_TEST_VAR=true');
        $config = (new ConfigFile($this->app['cwd'] . '/tests/config.php'))->config;

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
}
