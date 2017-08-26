<?php

namespace Tests;

use TightenCo\Jigsaw\Handlers\BladeHandler;
use TightenCo\Jigsaw\Handlers\MarkdownHandler;
use TightenCo\Jigsaw\IterableObject;
use TightenCo\Jigsaw\PageData;

class DotInFileNameTest extends TestCase
{
    public function test_md_files_with_dot_in_filename_are_processed()
    {
        $inputFile = $this->getInputFile('dot-files/filename-with.dot.1.md');
        $handler = $this->app->make(MarkdownHandler::class);
        $outputFile = $handler->handle($inputFile, $this->getPageDataDummy());

        $this->assertTrue($handler->shouldHandle($inputFile));
        $this->assertEquals('filename-with.dot.1', $outputFile[0]->name());
        $this->assertEquals('Test with dot in filename', $outputFile[0]->data()->page->title);
        $this->assertContains('<h3>This file contains a dot in the filename</h3>', $outputFile[0]->contents());
    }

    public function test_blade_md_hybrid_files_with_dot_in_filename_are_processed()
    {
        $inputFile = $this->getInputFile('dot-files/filename-with.dot.2.blade.md');
        $handler = $this->app->make(MarkdownHandler::class);
        $outputFile = $handler->handle($inputFile, $this->getPageDataDummy());

        $this->assertTrue($handler->shouldHandle($inputFile));
        $this->assertEquals('filename-with.dot.2', $outputFile[0]->name());
        $this->assertEquals('Second test with dot in filename', $outputFile[0]->data()->page->title);
        $this->assertContains('<h3>This file also contains a dot in the filename</h3>', $outputFile[0]->contents());
    }

    public function test_blade_files_with_dot_in_filename_are_processed()
    {
        $inputFile = $this->getInputFile('dot-files/filename-with.dot.3.blade.php');
        $handler = $this->app->make(BladeHandler::class);
        $outputFile = $handler->handle($inputFile, $this->getPageDataDummy());

        $this->assertTrue($handler->shouldHandle($inputFile));
        $this->assertEquals('filename-with.dot.3', $outputFile[0]->name());
        $this->assertEquals('Test with dot in filename', $outputFile[0]->data()->page->title);
        $this->assertContains('<h3>This file contains a dot in the filename</h3>', $outputFile[0]->contents());
    }

    protected function getPageDataDummy()
    {
        return PageData::withPageMetaData(new IterableObject(), []);
    }
}
