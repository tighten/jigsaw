<?php

namespace Tests;

use Illuminate\Container\Container;
use Illuminate\View\Component;

class BladeComponentTest extends TestCase
{
    /**
     * @test
     */
    public function can_include_blade_component_with_at_syntax()
    {
        $files = $this->setupSource([
            'page.blade.php' => implode("\n", [
                "@component('_components.alert')",
                "@slot('title') Title test @endslot",
                '<h1>Default content</h1>',
                '@endcomponent',
            ]),
            '_components' => [
                'alert.blade.php' => implode("\n", [
                    '<div>',
                    '<h3>This is the component</h3>',
                    '<h4>Named title slot: {{ $title }}</h4>',
                    '{{ $slot }}',
                    '</div>',
                ]),
            ],
        ]);

        $this->buildSite($files, []);

        $this->assertOutputFile(
            'build/page.html',
            <<<'HTML'
            <div>
            <h3>This is the component</h3>
            <h4>Named title slot: Title test</h4>
            <h1>Default content</h1>
            </div>
            HTML,
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
            '<h1>Hello</h1><div class="alert alert-error">The message</div>',
            $built,
        );
    }

    /**
     * @test
     */
    public function can_include_blade_component_with_x_tag_syntax_using_aliased_component_with_view()
    {
        $this->app['bladeCompiler']->component('alert', AlertComponent::class);

        $files = $this->setupSource([
            'page.blade.php' => '<h1>Hello</h1><x-alert type="error" message="The message"/>',
            '_components' => [
                'alert.blade.php' => '<div class="alert alert-{{ $type }}">{{ $message }}</div>',
            ],
        ]);

        $this->buildSite($files, []);

        $built = $files->getChild('build/page.html')->getContent();

        $this->assertEquals(
            '<h1>Hello</h1><div class="alert alert-error">The message</div>',
            $built,
        );
    }

    /**
     * @test
     */
    public function can_include_blade_component_with_x_tag_syntax_using_aliased_component_with_inline_render()
    {
        $this->app['bladeCompiler']->component('inline', InlineAlertComponent::class);

        $files = $this->setupSource([
            'page.blade.php' => '<h1>Hello</h1><x-inline type="error" message="The message"/>',
        ]);

        $this->buildSite($files, []);

        $built = $files->getChild('build/page.html')->getContent();

        $this->assertEquals(
            '<h1>Hello</h1><div class="alert alert-error">The message</div>',
            $built,
        );
    }

    /**
     * @test
     */
    public function can_include_blade_component_with_x_tag_syntax_using_namespaced_component_with_inline_render()
    {
        class_alias('Tests\InlineAlertComponent', 'Components\InlineClassComponent');

        $files = $this->setupSource([
            'page.blade.php' => '<h1>Hello</h1><x-inline-class-component type="error" message="The message"/>',
        ]);

        $this->buildSite($files, []);

        $built = $files->getChild('build/page.html')->getContent();

        $this->assertEquals(
            '<h1>Hello</h1><div class="alert alert-error">The message</div>',
            $built,
        );
    }

    /**
     * @test
     */
    public function can_include_blade_component_with_x_tag_syntax_using_namespaced_component_with_view()
    {
        class_alias('Tests\\AlertComponent', 'Components\\ClassComponent');

        $files = $this->setupSource([
            'page.blade.php' => '<h1>Hello</h1><x-class-component type="error" message="The message"/>',
            '_components' => [
                'alert.blade.php' => '<div class="alert alert-{{ $type }}">{{ $message }}</div>',
            ],
        ]);

        $this->buildSite($files, []);

        $built = $files->getChild('build/page.html')->getContent();

        $this->assertEquals(
            '<h1>Hello</h1><div class="alert alert-error">The message</div>',
            $built,
        );
    }
}

// phpcs:disable PSR1.Classes.ClassDeclaration,Squiz.Classes.ClassFileName
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
