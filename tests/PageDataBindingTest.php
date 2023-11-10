<?php

namespace Tests;

use Illuminate\Container\Container;
use Illuminate\Support\Str;
use Illuminate\View\Component;
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

        $files = $this->setupSource([
            'page.blade.php' => '<x-page-header/>',
        ]);

        $this->buildSite($files, []);

        $built = $files->getChild('build/page.html')->getContent();

        $data = json_decode(Str::between($built, '<div>Header: ', '</div>'), true);

        $this->assertEquals($data['page']['view.compiled'], getcwd() . '/cache');
        $this->assertSame('page', $data['page']['_meta']['filename']);
        $this->assertSame('/page.html', $data['page']['_meta']['path']);
        $this->assertSame('/page.html', $data['page']['_meta']['url']);
    }
}

// phpcs:disable PSR1.Classes.ClassDeclaration,Squiz.Classes.ClassFileName
class TestPageHeaderComponent extends Component
{
    public $page;

    public function __construct()
    {
        $this->page = Container::getInstance()->make('pageData');
    }

    public function render()
    {
        return '<div>Header: {!! json_encode($page) !!}</div>';
    }
}
