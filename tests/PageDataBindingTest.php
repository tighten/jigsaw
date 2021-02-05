<?php

namespace Tests;

use Illuminate\Container\Container;
use Illuminate\View\Component;
use Illuminate\Support\Str;
use org\bovigo\vfs\vfsStream;
use TightenCo\Jigsaw\PageData;

class PageDataBindingTest extends TestCase
{
    /** @test */
    public function can_bind_data_for_current_page_into_container()
    {
        class_alias('Tests\TestPageHeaderComponent', 'Components\PageHeader');

        $this->app->resolving('Tests\TestPageHeaderComponent', function ($component) {
            $this->assertTrue($component->page instanceof PageData);
        });

        $files = vfsStream::setup('virtual', null, [
            'source' => [
                'page.blade.php' => '<x-page-header/>',
            ],
        ]);

        $this->buildSite($files, []);

        $built = $files->getChild('build/page.html')->getContent();

        $data = json_decode(Str::between($built, '<div>Header: ', '</div>'), true);

        $this->assertTrue(Str::endsWith($data['page']['view.compiled'], '/jigsaw/cache'));
        $this->assertSame('page', $data['page']['_meta']['filename']);
        $this->assertSame('/page.html', $data['page']['_meta']['path']);
        $this->assertSame('/page.html', $data['page']['_meta']['url']);
    }
}

class TestPageHeaderComponent extends Component
{
    public $page;

    public function __construct()
    {
        $this->page = Container::getInstance()->make('pagedata');
    }

    public function render()
    {
        return '<div>Header: {!! json_encode($page) !!}</div>';
    }
}
