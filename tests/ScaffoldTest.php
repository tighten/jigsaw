<?php

namespace Tests;

use Symfony\Component\Console\Application;
use TightenCo\Jigsaw\Scaffold\BasicScaffold;
use org\bovigo\vfs\vfsStream;

class ScaffoldTest extends TestCase
{
    const EXISTING_FILES = [
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
        $vfs = vfsStream::setup('virtual', null, array_merge(
            self::EXISTING_FILES,
            ['archived' => []]
        ));
        $scaffold = $this->app->make(BasicScaffold::class)->setBase($vfs->url());

        $scaffold->archiveExistingSite();

        collect(self::EXISTING_FILES)->each(function ($file, $key) use ($vfs) {
            $this->assertNull($vfs->getChild($key));
        })->each(function ($file, $key) use ($vfs) {
            $this->assertNotNull($vfs->getChild('archived/' . $key));
        });
    }

    /**
     * @test
     */
    public function will_create_archived_directory_if_none_exists_when_archiving_site()
    {
        $vfs = vfsStream::setup('virtual', null, self::EXISTING_FILES);
        $scaffold = $this->app->make(BasicScaffold::class)->setBase($vfs->url());

        $scaffold->archiveExistingSite();

        collect(self::EXISTING_FILES)->each(function ($file, $key) use ($vfs) {
            $this->assertNull($vfs->getChild($key));
        })->each(function ($file, $key) use ($vfs) {
            $this->assertNotNull($vfs->getChild('archived/' . $key));
        });
    }

    /**
     * @test
     */
    public function will_erase_contents_of_archived_directory_if_it_already_exists_when_archiving_site()
    {
        $vfs = vfsStream::setup('virtual', null, array_merge(
            self::EXISTING_FILES,
            ['archived' => ['old-file.md' => '']]
        ));
        $scaffold = $this->app->make(BasicScaffold::class)->setBase($vfs->url());

        $this->assertNotNull($vfs->getChild('config.php'));
        $this->assertNotNull($vfs->getChild('archived/old-file.md'));

        $scaffold->archiveExistingSite();

        $this->assertNull($vfs->getChild('archived/old-file.md'));
    }

    /**
     * @test
     */
    public function will_ignore_archived_directory_when_archiving_site()
    {
        $vfs = vfsStream::setup('virtual', null, array_merge(
            self::EXISTING_FILES,
            ['archived' => []]
        ));
        $scaffold = $this->app->make(BasicScaffold::class)->setBase($vfs->url());

        $scaffold->archiveExistingSite();

        $this->assertNotNull($vfs->getChild('archived'));
        $this->assertNull($vfs->getChild('archived/archived'));
    }

    /**
     * @test
     */
    public function will_ignore_vendor_directory_when_archiving_site()
    {
        $vfs = vfsStream::setup('virtual', null, array_merge(
            self::EXISTING_FILES,
            ['vendor' => []]
        ));
        $scaffold = $this->app->make(BasicScaffold::class)->setBase($vfs->url());

        $scaffold->archiveExistingSite();

        $this->assertNotNull($vfs->getChild('vendor'));
        $this->assertNull($vfs->getChild('archived/vendor'));
    }

    /**
     * @test
     */
    public function will_ignore_node_modules_directory_when_archiving_site()
    {
        $vfs = vfsStream::setup('virtual', null, array_merge(
            self::EXISTING_FILES,
            ['node_modules' => []]
        ));
        $scaffold = $this->app->make(BasicScaffold::class)->setBase($vfs->url());

        $scaffold->archiveExistingSite();

        $this->assertNotNull($vfs->getChild('node_modules'));
        $this->assertNull($vfs->getChild('archived/node_modules'));
    }

    /**
     * @test
     */
    public function can_delete_existing_files_and_directories()
    {
        $vfs = vfsStream::setup('virtual', null, self::EXISTING_FILES);
        $scaffold = $this->app->make(BasicScaffold::class)->setBase($vfs->url());

        $scaffold->deleteExistingSite();

        collect(self::EXISTING_FILES)->each(function ($file, $key) use ($vfs) {
            $this->assertNull($vfs->getChild($key));
        });
    }

    /**
     * @test
     */
    public function will_ignore_archived_directory_when_deleting_site()
    {
        $vfs = vfsStream::setup('virtual', null, array_merge(
            self::EXISTING_FILES,
            ['archived' => []]
        ));
        $scaffold = $this->app->make(BasicScaffold::class)->setBase($vfs->url());

        $scaffold->deleteExistingSite();

        $this->assertNotNull($vfs->getChild('archived'));
    }

    /**
     * @test
     */
    public function will_ignore_vendor_directory_when_deleting_site()
    {
        $vfs = vfsStream::setup('virtual', null, array_merge(
            self::EXISTING_FILES,
            ['vendor' => []]
        ));
        $scaffold = $this->app->make(BasicScaffold::class)->setBase($vfs->url());

        $scaffold->deleteExistingSite();

        $this->assertNotNull($vfs->getChild('vendor'));
    }

    /**
     * @test
     */
    public function will_ignore_node_modules_directory_when_deleting_site()
    {
        $vfs = vfsStream::setup('virtual', null, array_merge(
            self::EXISTING_FILES,
            ['node_modules' => []]
        ));
        $scaffold = $this->app->make(BasicScaffold::class)->setBase($vfs->url());

        $scaffold->deleteExistingSite();

        $this->assertNotNull($vfs->getChild('node_modules'));
    }

    /**
     * @test
     */
    public function jigsaw_dependency_is_restored_to_fresh_composer_dot_json_when_archiving_site()
    {
        $old_composer = ['require' => ['tightenco/jigsaw' => '^1.2']];
        $existing_site = ['composer.json' => json_encode($old_composer)];
        $vfs = vfsStream::setup('virtual', null, $existing_site);
        $scaffold = $this->app->make(BasicScaffold::class)->setBase($vfs->url());

        $scaffold->archiveExistingSite();

        $this->assertEquals($old_composer, json_decode($vfs->getChild('composer.json')->getContent(), true));
    }

    /**
     * @test
     */
    public function composer_dot_json_is_not_restored_if_it_did_not_exist_when_archiving_site()
    {
        $vfs = vfsStream::setup('virtual', null, self::EXISTING_FILES);
        $scaffold = $this->app->make(BasicScaffold::class)->setBase($vfs->url());

        $scaffold->archiveExistingSite();

        $this->assertNull($vfs->getChild('composer.json'));
    }

    /**
     * @test
     */
    public function jigsaw_dependency_is_restored_to_fresh_composer_dot_json_when_deleting_site()
    {
        $old_composer = ['require' => ['tightenco/jigsaw' => '^1.2']];
        $existing_site = ['composer.json' => json_encode($old_composer)];
        $vfs = vfsStream::setup('virtual', null, $existing_site);
        $scaffold = $this->app->make(BasicScaffold::class)->setBase($vfs->url());

        $scaffold->deleteExistingSite();

        $this->assertEquals($old_composer, json_decode($vfs->getChild('composer.json')->getContent(), true));
    }

    /**
     * @test
     */
    public function composer_dot_json_is_not_restored_if_it_did_not_exist_when_deleting_site()
    {
        $vfs = vfsStream::setup('virtual', null, self::EXISTING_FILES);
        $scaffold = $this->app->make(BasicScaffold::class)->setBase($vfs->url());

        $scaffold->deleteExistingSite();

        $this->assertNull($vfs->getChild('composer.json'));
    }
}
