<?php

namespace Tests;

class HelpersTest extends TestCase
{
    /**
     * @test
     */
    public function public_path_returns_path_to_specified_file_in_default_source_directory()
    {
        $this->assertEquals('source/some-file.md', public_path('some-file.md'));
    }

    /**
     * @test
     */
    public function public_path_returns_path_to_specified_file_in_custom_source_directory()
    {
        $this->app->config = collect([
            'build' => [
                'source' => 'src',
            ],
        ]);

        $this->assertEquals('src/some-file.md', public_path('some-file.md'));
    }

    /**
     * @test
     */
    public function leftTrimPath_leaves_leading_periods()
    {
        $this->assertEquals('.well-known', leftTrimPath('.well-known'));
    }
}
