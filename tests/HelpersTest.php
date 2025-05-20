<?php

namespace Tests;

use PHPUnit\Framework\Attributes\Test;

class HelpersTest extends TestCase
{
    #[Test]
    public function public_path_returns_path_to_specified_file_in_default_source_directory()
    {
        $this->assertEquals('source/some-file.md', public_path('some-file.md'));
    }

    #[Test]
    public function public_path_returns_path_to_specified_file_in_custom_source_directory()
    {
        $this->app->config = collect([
            'build' => [
                'source' => 'src',
            ],
        ]);

        $this->assertEquals('src/some-file.md', public_path('some-file.md'));
    }

    #[Test]
    public function leftTrimPath_leaves_leading_periods()
    {
        $this->assertEquals('.well-known', leftTrimPath('.well-known'));
    }

    #[Test]
    public function url_helper_returns_absolute_url()
    {
        $this->app->config = collect(['baseUrl' => 'https://test.com/sub/']);

        $this->assertSame('https://test.com/sub/posts/my-first-post', url('posts/my-first-post'));
    }

    #[Test]
    public function resolve_path_does_not_strip_0s()
    {
        $path = 'path/to/assets/0/0/0.png';
        $normalizedOutput = str_replace(['/', '\\'], '/', resolvePath($path));

        $this->assertSame($path, $normalizedOutput);
    }
}
