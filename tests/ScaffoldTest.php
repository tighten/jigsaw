<?php

namespace Tests;

use TightenCo\Jigsaw\Scaffold\BasicScaffoldBuilder;

class ScaffoldTest extends TestCase
{
    public const EXISTING_FILES = [
        '.gitignore' => '',
        'bootstrap.php' => '',
        'config.php' => '',
        'gulpfile.js' => '',
        'source' => [
            'test-source-file.md' => '',
        ],
        'tasks' => [],
        'webpack.mix.js' => '',
        'yarn.lock' => '',
    ];

    /**
     * @test
     */
    public function can_archive_existing_files_and_directories()
    {
        $this->createSource(array_merge(
            self::EXISTING_FILES,
            ['archived' => []],
        ));
        $scaffold = $this->app->make(BasicScaffoldBuilder::class)->setBase($this->tmp);

        $scaffold->archiveExistingSite();

        collect(self::EXISTING_FILES)->each(function ($file, $key) {
            $this->assertFileMissing($this->tmpPath($key));
        })->each(function ($file, $key) {
            $this->assertFileExists($this->tmpPath('archived/' . $key));
        });
    }

    /**
     * @test
     */
    public function will_create_archived_directory_if_none_exists_when_archiving_site()
    {
        $this->createSource(self::EXISTING_FILES);
        $scaffold = $this->app->make(BasicScaffoldBuilder::class)->setBase($this->tmp);

        $scaffold->archiveExistingSite();

        collect(self::EXISTING_FILES)->each(function ($file, $key) {
            $this->assertFileMissing($this->tmpPath($key));
        })->each(function ($file, $key) {
            $this->assertFileExists($this->tmpPath('archived/' . $key));
        });
    }

    /**
     * @test
     */
    public function will_erase_contents_of_archived_directory_if_it_already_exists_when_archiving_site()
    {
        $this->createSource(array_merge(
            self::EXISTING_FILES,
            ['archived' => ['old-file.md' => '']],
        ));
        $scaffold = $this->app->make(BasicScaffoldBuilder::class)->setBase($this->tmp);

        $this->assertFileExists($this->tmpPath('config.php'));
        $this->assertFileExists($this->tmpPath('archived/old-file.md'));

        $scaffold->archiveExistingSite();

        $this->assertFileMissing($this->tmpPath('archived/old-file.md'));
    }

    /**
     * @test
     */
    public function will_ignore_archived_directory_when_archiving_site()
    {
        $this->createSource(array_merge(
            self::EXISTING_FILES,
            ['archived' => []],
        ));
        $scaffold = $this->app->make(BasicScaffoldBuilder::class)->setBase($this->tmp);

        $scaffold->archiveExistingSite();

        $this->assertFileExists($this->tmpPath('archived'));
        $this->assertFileMissing($this->tmpPath('archived/archived'));
    }

    /**
     * @test
     */
    public function will_ignore_vendor_directory_when_archiving_site()
    {
        $this->createSource(array_merge(
            self::EXISTING_FILES,
            ['vendor' => []],
        ));
        $scaffold = $this->app->make(BasicScaffoldBuilder::class)->setBase($this->tmp);

        $scaffold->archiveExistingSite();

        $this->assertFileExists($this->tmpPath('vendor'));
        $this->assertFileMissing($this->tmpPath('archived/vendor'));
    }

    /**
     * @test
     */
    public function will_ignore_node_modules_directory_when_archiving_site()
    {
        $this->createSource(array_merge(
            self::EXISTING_FILES,
            ['node_modules' => []],
        ));
        $scaffold = $this->app->make(BasicScaffoldBuilder::class)->setBase($this->tmp);

        $scaffold->archiveExistingSite();

        $this->assertFileExists($this->tmpPath('node_modules'));
        $this->assertFileMissing($this->tmpPath('archived/node_modules'));
    }

    /**
     * @test
     */
    public function can_delete_existing_files_and_directories()
    {
        $this->createSource(self::EXISTING_FILES);
        $scaffold = $this->app->make(BasicScaffoldBuilder::class)->setBase($this->tmp);

        $scaffold->deleteExistingSite();

        collect(self::EXISTING_FILES)->each(function ($file, $key) {
            $this->assertFileMissing($this->tmpPath($key));
        });
    }

    /**
     * @test
     */
    public function will_ignore_archived_directory_when_deleting_site()
    {
        $this->createSource(array_merge(
            self::EXISTING_FILES,
            ['archived' => []],
        ));
        $scaffold = $this->app->make(BasicScaffoldBuilder::class)->setBase($this->tmp);

        $scaffold->deleteExistingSite();

        $this->assertFileExists($this->tmpPath('archived'));
    }

    /**
     * @test
     */
    public function will_ignore_vendor_directory_when_deleting_site()
    {
        $this->createSource(array_merge(
            self::EXISTING_FILES,
            ['vendor' => []],
        ));
        $scaffold = $this->app->make(BasicScaffoldBuilder::class)->setBase($this->tmp);

        $scaffold->deleteExistingSite();

        $this->assertFileExists($this->tmpPath('vendor'));
    }

    /**
     * @test
     */
    public function will_ignore_node_modules_directory_when_deleting_site()
    {
        $this->createSource(array_merge(
            self::EXISTING_FILES,
            ['node_modules' => []],
        ));
        $scaffold = $this->app->make(BasicScaffoldBuilder::class)->setBase($this->tmp);

        $scaffold->deleteExistingSite();

        $this->assertFileExists($this->tmpPath('node_modules'));
    }

    /**
     * @test
     */
    public function jigsaw_dependency_is_restored_to_fresh_composer_dot_json_when_archiving_site()
    {
        $old_composer = ['require' => ['tightenco/jigsaw' => '^1.2']];
        $existing_site = ['composer.json' => json_encode($old_composer)];
        $this->createSource($existing_site);
        $scaffold = $this->app->make(BasicScaffoldBuilder::class)->setBase($this->tmp);

        $scaffold->archiveExistingSite();

        $this->assertEquals($old_composer, json_decode(file_get_contents($this->tmpPath('composer.json')), true));
    }

    /**
     * @test
     */
    public function composer_dot_json_is_not_restored_if_it_did_not_exist_when_archiving_site()
    {
        $this->createSource(self::EXISTING_FILES);
        $scaffold = $this->app->make(BasicScaffoldBuilder::class)->setBase($this->tmp);

        $scaffold->archiveExistingSite();

        $this->assertFileMissing($this->tmpPath('composer.json'));
    }

    /**
     * @test
     */
    public function jigsaw_dependency_is_restored_to_fresh_composer_dot_json_when_deleting_site()
    {
        $old_composer = ['require' => ['tightenco/jigsaw' => '^1.2']];
        $existing_site = ['composer.json' => json_encode($old_composer)];
        $this->createSource($existing_site);
        $scaffold = $this->app->make(BasicScaffoldBuilder::class)->setBase($this->tmp);

        $scaffold->deleteExistingSite();

        $this->assertEquals($old_composer, json_decode(file_get_contents($this->tmpPath('composer.json')), true));
    }

    /**
     * @test
     */
    public function composer_dot_json_is_not_restored_if_it_did_not_exist_when_deleting_site()
    {
        $this->createSource(self::EXISTING_FILES);
        $scaffold = $this->app->make(BasicScaffoldBuilder::class)->setBase($this->tmp);

        $scaffold->deleteExistingSite();

        $this->assertFileMissing($this->tmpPath('composer.json'));
    }
}
