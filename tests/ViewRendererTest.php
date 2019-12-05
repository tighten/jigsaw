<?php

namespace Tests;

use Illuminate\View\Compilers\BladeCompiler;
use Illuminate\View\Factory;
use TightenCo\Jigsaw\View\ViewRenderer;
use Mockery;

class ViewRendererTest extends TestCase
{

    /**
     * @test
     */
    public function it_registers_hint_paths()
    {
        $mock = Mockery::mock(Factory::class);
        $mock->shouldReceive('getFinder');
        $mock->shouldReceive('addNamespace')->with('view::hint', 'path');
        $mock->shouldReceive('addExtension');
        new ViewRenderer($mock, Mockery::mock(BladeCompiler::class), [
            'viewHintPaths' => [
                'view::hint' => 'path'
            ]
        ]);

        $this->addToAssertionCount(
            Mockery::getContainer()->mockery_getExpectationCount()
        );
    }

    /**
     * @test
     */
    public function it_does_not_register_not_exists()
    {
        $mock = Mockery::mock(Factory::class);
        $mock->shouldReceive('getFinder');
        $mock->shouldNotReceive('addNamespace')->with('view::hint', 'path');
        $mock->shouldReceive('addExtension');
        new ViewRenderer($mock, Mockery::mock(BladeCompiler::class));
        $this->addToAssertionCount(
            Mockery::getContainer()->mockery_getExpectationCount()
        );
    }

    /**
     * @test
     */
    public function test_it_does_not_register_empty()
    {
        $mock = Mockery::mock(Factory::class);
        $mock->shouldReceive('getFinder');
        $mock->shouldNotReceive('addNamespace')->with('view::hint', 'path');
        $mock->shouldReceive('addExtension');
        new ViewRenderer($mock, Mockery::mock(BladeCompiler::class), [
            'viewHintPaths' => []
        ]);
        $this->addToAssertionCount(
            Mockery::getContainer()->mockery_getExpectationCount()
        );
    }
}