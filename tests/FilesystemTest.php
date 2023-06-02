<?php

namespace Tests;

use TightenCo\Jigsaw\File\Filesystem;

class FilesystemTest extends TestCase
{
    public const TEST_FILES = [
        '.dotfile' => '',
        'file-1.md' => '',
        'file-2.md' => '',
        'directory' => [
            'nested-file-1.md' => '',
            'nested-file-2.md' => '',
            'nested-directory' => [
                'double-nested-file-1.md' => '',
                'double-nested-file-2.md' => '',
            ],
            'nested-empty-directory' => [],
        ],
        'empty-directory' => [],
    ];

    /**
     * @test
     */
    public function can_return_array_of_all_files_and_directories()
    {
        $filesystem = $this->app->make(Filesystem::class);
        $vfs = $this->setupFiles();

        $files = $filesystem->filesAndDirectories($this->tmp);

        $this->assertCount(11, $files);
    }

    /**
     * @test
     */
    public function DS_Store_is_always_ignored_when_retrieving_all_files_and_directories()
    {
        $this->createSource([
            'test-file.md' => '',
            '.gitignore' => '',
            '.DS_Store' => '',
        ]);

        $files = collect(
            $this->app->make(Filesystem::class)
            ->filesAndDirectories($this->tmp),
        )->map(function ($file) {
            return $file->getRelativePathName();
        });

        $this->assertContains('test-file.md', $files);
        $this->assertContains('.gitignore', $files);
        $this->assertNotContains('.DS_Store', $files);
        $this->assertCount(2, $files);
    }

    /**
     * @test
     */
    public function can_ignore_a_file_when_retrieving_all_files_and_directories()
    {
        $files = $this->getFilesExcept('file-1.md');

        $this->assertNotContains('file-1.md', $files);
        $this->assertCount(10, $files);
    }

    /**
     * @test
     */
    public function can_ignore_multiple_files_when_retrieving_all_files_and_directories()
    {
        $files = $this->getFilesExcept([
            'file-1.md',
            'file-2.md',
        ]);

        $this->assertNotContains('file-1.md', $files);
        $this->assertNotContains('file-2.md', $files);
        $this->assertCount(9, $files);
    }

    /**
     * @test
     */
    public function can_use_wildcard_to_ignore_files_when_retrieving_all_files_and_directories()
    {
        $files = $this->getFilesExcept([
            'file-*',
        ]);

        $this->assertNotContains('file-1.md', $files);
        $this->assertNotContains('file-2.md', $files);
        $this->assertCount(9, $files);
    }

    /**
     * @test
     */
    public function can_use_wildcard_in_middle_of_filename_to_ignore_files_when_retrieving_all_files_and_directories()
    {
        $files = $this->getFilesExcept([
            'file-*.md',
        ]);

        $this->assertNotContains('file-1.md', $files);
        $this->assertNotContains('file-2.md', $files);
        $this->assertCount(9, $files);
    }

    /**
     * @test
     */
    public function can_use_wildcard_at_beginning_of_filename_to_ignore_files_when_retrieving_all_files_and_directories()
    {
        $files = $this->getFilesExcept([
            '*.md',
        ]);

        $this->assertNotContains('file-1.md', $files);
        $this->assertNotContains('file-2.md', $files);
        $this->assertCount(9, $files);
    }

    /**
     * @test
     */
    public function can_ignore_directories_when_retrieving_all_files_and_directories()
    {
        $files = $this->getFilesExcept([
            'directory',
        ]);

        $this->assertNotContains('directory', $files);
        $this->assertCount(4, $files);
    }

    /**
     * @test
     */
    public function directory_slash_is_ignored_when_retrieving_all_files_and_directories()
    {
        $files = $this->getFilesExcept([
            'directory/',
        ]);

        $this->assertNotContains('directory', $files);
        $this->assertCount(4, $files);
    }

    /**
     * @test
     */
    public function can_ignore_nested_directories_when_retrieving_all_files_and_directories()
    {
        $files = $this->getFilesExcept([
            'directory/nested-directory',
        ]);

        $this->assertNotContains($this->fixDirectorySlashes('directory/nested-directory'), $files);
        $this->assertCount(8, $files);
    }

    /**
     * @test
     */
    public function can_use_wildcard_to_ignore_nested_files_when_retrieving_all_files_and_directories()
    {
        $files = $this->getFilesExcept([
            'directory/nested-directory/*',
        ]);

        $this->assertContains($this->fixDirectorySlashes('directory/nested-directory'), $files);
        $this->assertNotContains($this->fixDirectorySlashes('directory/nested-directory/nested-file-1.md'), $files);
        $this->assertCount(9, $files);
    }

    /**
     * @test
     */
    public function can_use_multiple_wildcards_to_ignore_files_when_retrieving_all_files_and_directories()
    {
        $files = $this->getFilesExcept([
            'directory/nested-directory/double-*-file-*.md',
        ]);

        $this->assertNotContains($this->fixDirectorySlashes('directory/nested-directory/double-nested-file-1.md'), $files);
        $this->assertNotContains($this->fixDirectorySlashes('directory/nested-directory/double-nested-file-1.md'), $files);
        $this->assertCount(9, $files);
    }

    /**
     * @test
     */
    public function can_return_array_of_files_and_directories_matching_a_string()
    {
        $filesystem = $this->app->make(Filesystem::class);
        $vfs = $this->setupFiles();

        $files = $filesystem->filesAndDirectories($this->tmp, 'file-1.md');

        $this->assertCount(1, $files);
        $this->assertEquals('file-1.md', $files[0]->getFileName());
    }

    /**
     * @test
     */
    public function can_return_array_of_files_and_directories_matching_an_array()
    {
        $files = $this->getFilesMatching([
            'file-1.md',
            'file-2.md',
        ]);

        $this->assertEqualsCanonicalizing(['file-1.md', 'file-2.md'], $files->all());
    }

    /**
     * @test
     */
    public function can_return_array_of_files_and_directories_matching_a_wildcard()
    {
        $files = $this->getFilesMatching([
            'file-*.md',
        ]);

        $this->assertEqualsCanonicalizing(['file-1.md', 'file-2.md'], $files->all());
    }

    /**
     * @test
     */
    public function can_return_array_of_files_and_directories_matching_a_directory()
    {
        $files = $this->getFilesMatching([
            'directory',
        ]);

        $this->assertEqualsCanonicalizing([
            'directory',
            $this->fixDirectorySlashes('directory/nested-directory'),
            $this->fixDirectorySlashes('directory/nested-directory/double-nested-file-1.md'),
            $this->fixDirectorySlashes('directory/nested-directory/double-nested-file-2.md'),
            $this->fixDirectorySlashes('directory/nested-empty-directory'),
            $this->fixDirectorySlashes('directory/nested-file-1.md'),
            $this->fixDirectorySlashes('directory/nested-file-2.md'),
        ], $files->all());
    }

    protected function getFilesMatching($match)
    {
        $this->setupFiles();

        return collect(
            $this->app->make(Filesystem::class)
            ->filesAndDirectories($this->tmp, $match),
        )->map(function ($file) {
            return $file->getRelativePathName();
        });
    }

    protected function getFilesExcept($ignore)
    {
        $this->setupFiles();

        return collect(
            $this->app->make(Filesystem::class)
            ->filesAndDirectories($this->tmp, null, $ignore),
        )->map(function ($file) {
            return $file->getRelativePathName();
        });
    }

    protected function setupFiles()
    {
        $this->createSource(self::TEST_FILES);
    }
}
