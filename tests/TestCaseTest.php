<?php

namespace Tests;

class TestCaseTest extends TestCase
{
    /** @test */
    public function create_test_file_structure_from_nested_array()
    {
        $this->createSource([
            'content' => [
                'pages' => [],
                'posts' => [
                    'unique.md' => 'New York',
                    '2020' => [
                        'stay.md' => 'Home',
                    ],
                ],
                'index.md' => 'Welcome',
            ],
            'public' => [],
            'resources' => [
                'js' => [
                    'main.js' => 'console.log("Look ma, no file system!");',
                ],
            ],
        ]);

        $this->assertDirectoryExists($this->tmpPath('content'));
        $this->assertDirectoryExists($this->tmpPath('content/pages'));
        $this->assertEmpty(app('files')->files($this->tmpPath('content/pages')));
        $this->assertDirectoryExists($this->tmpPath('content/posts'));
        $this->assertStringEqualsFile($this->tmpPath('content/posts/unique.md'), 'New York');
        $this->assertDirectoryExists($this->tmpPath('content/posts/2020/'));
        $this->assertStringEqualsFile($this->tmpPath('content/posts/2020/stay.md'), 'Home');
        $this->assertStringEqualsFile($this->tmpPath('content/index.md'), 'Welcome');
        $this->assertDirectoryExists($this->tmpPath('public'));
        $this->assertEmpty(app('files')->files($this->tmpPath('public')));
        $this->assertDirectoryExists($this->tmpPath('resources'));
        $this->assertDirectoryExists($this->tmpPath('resources/js'));
        $this->assertStringEqualsFile($this->tmpPath('resources/js/main.js'), 'console.log("Look ma, no file system!");');
    }

    /** @test */
    public function create_test_files_with_multiple_extensions()
    {
        $this->createSource([
            'content' => [
                'pages' => [
                    'one.blade.md' => 'Two',
                    'one' => [
                        'two.blade.php' => 'Three',
                    ],
                ],
            ],
        ]);

        $this->assertDirectoryExists($this->tmpPath('content'));
        $this->assertDirectoryExists($this->tmpPath('content/pages'));
        $this->assertStringEqualsFile($this->tmpPath('content/pages/one.blade.md'), 'Two');
        $this->assertDirectoryExists($this->tmpPath('content/pages/one'));
        $this->assertStringEqualsFile($this->tmpPath('content/pages/one/two.blade.php'), 'Three');
    }
}
