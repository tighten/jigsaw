<?php

namespace Tests;

use TightenCo\Jigsaw\IterableObject;
use TightenCo\Jigsaw\PageVariable;
use TightenCo\Jigsaw\PathResolvers\CollectionPathResolver;
use TightenCo\Jigsaw\PathResolvers\PrettyOutputPathResolver;

class FilePathTest extends TestCase
{
    public function test_accented_character_in_filename_is_replaced_with_unaccented_character_when_using_default_path_config()
    {
        $this->app->instance('outputPathResolver', new PrettyOutputPathResolver());
        $pathResolver = $this->app->make(CollectionPathResolver::class);
        $pageVariable = $this->getPageVariableDummy('Fútbol solidario');
        $outputPath = $pathResolver->link(null, $pageVariable);

        $this->assertEquals('/futbol-solidario', $outputPath[0]);
    }

    public function test_accented_character_in_filename_is_replaced_with_unaccented_character_when_using_shorthand_path_config()
    {
        $this->app->instance('outputPathResolver', new PrettyOutputPathResolver());
        $pathResolver = $this->app->make(CollectionPathResolver::class);
        $pageVariable = $this->getPageVariableDummy('Fútbol solidario');
        $outputPath = $pathResolver->link('{filename}', $pageVariable);

        $this->assertEquals('/Futbol solidario', $outputPath[0]);
    }

    public function test_accented_character_in_filename_is_replaced_with_unaccented_character_when_using_slugified_shorthand_path_config()
    {
        $this->app->instance('outputPathResolver', new PrettyOutputPathResolver());
        $pathResolver = $this->app->make(CollectionPathResolver::class);
        $pageVariable = $this->getPageVariableDummy('Fútbol solidario');
        $outputPath = $pathResolver->link('{_filename}', $pageVariable);

        $this->assertEquals('/futbol_solidario', $outputPath[0]);
    }

    public function test_invalid_characters_in_filename_are_removed_when_using_default_path_config()
    {
        $this->app->instance('outputPathResolver', new PrettyOutputPathResolver());
        $pathResolver = $this->app->make(CollectionPathResolver::class);
        $pageVariable = $this->getPageVariableDummy('Has® Invalid™ Characters');
        $outputPath = $pathResolver->link(null, $pageVariable);

        $this->assertEquals('/has-invalid-characters', $outputPath[0]);
    }

    public function test_invalid_characters_in_filename_are_removed_when_using_shorthand_path_config()
    {
        $this->app->instance('outputPathResolver', new PrettyOutputPathResolver());
        $pathResolver = $this->app->make(CollectionPathResolver::class);
        $pageVariable = $this->getPageVariableDummy('Has® Invalid™ Characters');
        $outputPath = $pathResolver->link('{filename}', $pageVariable);

        $this->assertEquals('/Has Invalid Characters', $outputPath[0]);
    }

    public function test_invalid_characters_in_filename_are_removed_when_using_slugified_shorthand_path_config()
    {
        $this->app->instance('outputPathResolver', new PrettyOutputPathResolver());
        $pathResolver = $this->app->make(CollectionPathResolver::class);
        $pageVariable = $this->getPageVariableDummy('Has® Invalid™ Characters');
        $outputPath = $pathResolver->link('{_filename}', $pageVariable);

        $this->assertEquals('/has_invalid_characters', $outputPath[0]);
    }

    public function test_international_characters_in_filename_are_allowed_when_using_default_path_config()
    {
        $this->app->instance('outputPathResolver', new PrettyOutputPathResolver());
        $pathResolver = $this->app->make(CollectionPathResolver::class);
        $pageVariable = $this->getPageVariableDummy('테스트-파일-이름');
        $outputPath = $pathResolver->link(null, $pageVariable);

        $this->assertEquals('/테스트-파일-이름', $outputPath[0]);
    }

    public function test_international_characters_in_filename_are_allowed_when_using_shorthand_path_config()
    {
        $this->app->instance('outputPathResolver', new PrettyOutputPathResolver());
        $pathResolver = $this->app->make(CollectionPathResolver::class);
        $pageVariable = $this->getPageVariableDummy('테스트-파일-이름');
        $outputPath = $pathResolver->link('{filename}', $pageVariable);

        $this->assertEquals('/테스트-파일-이름', $outputPath[0]);
    }

    public function test_international_characters_in_filename_are_allowed_when_using_slugified_shorthand_path_config()
    {
        $this->app->instance('outputPathResolver', new PrettyOutputPathResolver());
        $pathResolver = $this->app->make(CollectionPathResolver::class);
        $pageVariable = $this->getPageVariableDummy('테스트-파일-이름');
        $outputPath = $pathResolver->link('{_filename}', $pageVariable);

        $this->assertEquals('/테스트_파일_이름', $outputPath[0]);
    }

    protected function getPageVariableDummy($filename)
    {
        return new PageVariable([
            'extends' => '_layouts/test-base',
            '_meta' => new IterableObject([
                'collectionName' => '',
                'filename' => $filename,
            ]),
        ]);
    }
}
