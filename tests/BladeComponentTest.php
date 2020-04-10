<?php

namespace Tests;

use Illuminate\Container\Container;
use Illuminate\View\Component;
use Illuminate\View\View;
use org\bovigo\vfs\vfsStream;

class BladeComponentTest extends TestCase
{
    /**
     * @test
     */
    public function can_include_blade_component_with_at_syntax()
    {
        $files = $this->setupSource([
            'page.blade.php' => <<<'blade'
            @component('_components.alert')
                @slot('title')
                    Title test
                @endslot
                <h1>Default content</h1>
            @endcomponent
            blade,
            '_components' => [
                'alert.blade.php' => <<<'component'
                <div>
                    <h3>This is the component</h3>
                    <h4>Named title slot: {{ $title }}</h4>
                    {{ $slot }}
                </div>
                component,
            ],
        ]);

        $this->buildSite($files, []);

        $built = $files->getChild('build/page.html')->getContent();

        $this->assertEquals(
            "<div>\n" .
            "    <h3>This is the component</h3>\n" .
            "    <h4>Named title slot: Title test</h4>\n" .
            "    <h1>Default content</h1>\n" .
            "</div>",
            $built
        );
    }

    /**
     * @test
     */
    public function can_include_blade_component_with_x_tag_syntax_using_underscore_components_directory()
    {
        $files = $this->setupSource([
            'page.blade.php' => '<h1>Hello</h1><x-alert type="error" message="The message"/>',
            '_components' => [
                'alert.blade.php' => '<div class="alert alert-{{ $type }}">{{ $message }}</div>',
            ],
        ]);

        $this->buildSite($files, []);

        $built = $files->getChild('build/page.html')->getContent();

        $this->assertEquals(
            '<h1>Hello</h1> <div class="alert alert-error">The message</div> ',
            $built
        );
    }

    /**
     * @test
     */
    public function can_include_blade_component_with_x_tag_syntax_using_aliased_component_with_view()
    {
        $this->app['bladeCompiler']->component('alert', AlertComponent::class);

        $files = vfsStream::setup('virtual', null, [
            'source' => [
                'page.blade.php' => '<h1>Hello</h1><x-alert type="error" message="The message"/>',
                '_components' => [
                    'alert.blade.php' => '<div class="alert alert-{{ $type }}">{{ $message }}</div>',
                ],
            ],
        ]);

        $this->buildSite($files, []);

        $built = $files->getChild('build/page.html')->getContent();

        $this->assertEquals(
            '<h1>Hello</h1> <div class="alert alert-error">The message</div> ',
            $built
        );
    }

    /**
     * @test
     */
    public function can_include_blade_component_with_x_tag_syntax_using_aliased_component_with_inline_render()
    {
        $this->app['bladeCompiler']->component('inline', InlineAlertComponent::class);

        $files = vfsStream::setup('virtual', null, [
            'source' => [
                'page.blade.php' => '<h1>Hello</h1><x-inline type="error" message="The message"/>',
            ],
        ]);

        $this->buildSite($files, []);

        $built = $files->getChild('build/page.html')->getContent();

        $this->assertEquals(
            '<h1>Hello</h1> <div class="alert alert-error">The message</div> ',
            $built
        );
    }
}

class AlertComponent extends Component
{
    public $type;
    public $message;

    public function __construct($type, $message)
    {
        $this->type = $type;
        $this->message = $message;
    }

    public function render()
    {
        return Container::getInstance()->make('view')->make('_components.alert');
    }
}

class InlineAlertComponent extends Component
{
    public $type;
    public $message;

    public function __construct($type, $message)
    {
        $this->type = $type;
        $this->message = $message;
    }

    public function render()
    {
        return '<div class="alert alert-{{ $type }}">{{ $message }}</div>';
    }
}
