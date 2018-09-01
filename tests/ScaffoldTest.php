<?php

namespace Tests;

use Symfony\Component\Console\Application;
use TightenCo\Jigsaw\Scaffold\BasicScaffold;
use org\bovigo\vfs\vfsStream;

class ScaffoldTest extends TestCase
{
    const EXISTING_SITE_FILES = [
        '.gitignore' => '',
        'bootstrap.php' => '',
        'config.php' => '',
        'source' => [
            'test-source-file.md' => '',
            ],
    ];

    /**
     * @test
     */
    public function can_build_list_of_base_site_files()
    {
        $base_files = [
            '.gitignore',
            'bootstrap.php',
            'composer.json',
            'composer.lock',
            'config.php',
            'gulpfile.js',
            'package.json',
            'package.lock',
            'source/',
            'tasks/',
            'webpack.mix.js',
            'yarn.lock',
        ];
        sort($base_files);

        $scaffold = $this->app->make(BasicScaffold::class);

        $this->assertEquals($base_files, $scaffold->getSiteFiles()->sort()->values()->toArray());
    }

    /**
     * @test
     */
    public function can_archive_existing_files_and_directories()
    {
        $vfs = vfsStream::setup('virtual', null, array_merge(
            self::EXISTING_SITE_FILES,
            ['archived' => []]
        ));
        $scaffold = $this->app->make(BasicScaffold::class)->setBase($vfs->url());

        $scaffold->archiveExistingSite();

        collect(self::EXISTING_SITE_FILES)->each(function ($file, $key) use ($vfs) {
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
        $vfs = vfsStream::setup('virtual', null, self::EXISTING_SITE_FILES);
        $scaffold = $this->app->make(BasicScaffold::class)->setBase($vfs->url());

        $scaffold->archiveExistingSite();

        collect(self::EXISTING_SITE_FILES)->each(function ($file, $key) use ($vfs) {
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
            self::EXISTING_SITE_FILES,
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
    public function will_ignore_base_files_that_do_not_exist_when_archiving_site()
    {
        $subset_of_base_files = self::EXISTING_SITE_FILES;
        unset($subset_of_base_files['bootstrap.php']);
        $vfs = vfsStream::setup('virtual', null, $subset_of_base_files);
        $scaffold = $this->app->make(BasicScaffold::class)->setBase($vfs->url());

        $scaffold->archiveExistingSite();

        collect($subset_of_base_files)->each(function ($file, $key) use ($vfs) {
            $this->assertNull($vfs->getChild($key));
        })->each(function ($file, $key) use ($vfs) {
            $this->assertNotNull($vfs->getChild('archived/' . $key));
        });
    }

    /**
     * @test
     */
    public function can_delete_existing_files_and_directories()
    {
        $vfs = vfsStream::setup('virtual', null, self::EXISTING_SITE_FILES);
        $scaffold = $this->app->make(BasicScaffold::class)->setBase($vfs->url());

        $scaffold->deleteExistingSite();

        collect(self::EXISTING_SITE_FILES)->each(function ($file, $key) use ($vfs) {
            $this->assertNull($vfs->getChild($key));
        });
    }

    /**
     * @test
     */
    public function will_ignore_base_files_that_do_not_exist_when_deleting_site()
    {
        $subset_of_base_files = self::EXISTING_SITE_FILES;
        unset($subset_of_base_files['bootstrap.php']);
        $vfs = vfsStream::setup('virtual', null, $subset_of_base_files);
        $scaffold = $this->app->make(BasicScaffold::class)->setBase($vfs->url());

        $scaffold->deleteExistingSite();

        collect($subset_of_base_files)->each(function ($file, $key) use ($vfs) {
            $this->assertNull($vfs->getChild($key));
        });
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
        $vfs = vfsStream::setup('virtual', null, self::EXISTING_SITE_FILES);
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
        $vfs = vfsStream::setup('virtual', null, self::EXISTING_SITE_FILES);
        $scaffold = $this->app->make(BasicScaffold::class)->setBase($vfs->url());

        $scaffold->deleteExistingSite();

        $this->assertNull($vfs->getChild('composer.json'));
    }
}
