<?php

namespace Tests;

use TightenCo\Jigsaw\Jigsaw;
use org\bovigo\vfs\vfsStream;

class EventsTest extends TestCase
{
    public function test_user_can_add_event_listeners_as_closures()
    {
        $this->app['events']->beforeBuild(function ($jigsaw) use (&$a) {
            $a = $jigsaw->getConfig('a');
        });
        $this->app['events']->afterCollections(function ($jigsaw) use (&$b) {
            $b = $jigsaw->getConfig('b');
        });
        $this->app['events']->afterBuild(function ($jigsaw) use (&$c) {
            $c = $jigsaw->getConfig('c');
        });
        $this->buildSite($this->setupSource(), [
            'a' => 123,
            'b' => 456,
            'c' => 789,
        ]);

        $this->assertEquals(123, $a);
        $this->assertEquals(456, $b);
        $this->assertEquals(789, $c);
    }

    public function test_user_can_add_event_listeners_as_classes()
    {
        $this->app['events']->beforeBuild(TestListener::class);
        $jigsaw = $this->buildSite($this->setupSource(), ['variable_a' => 'set in config.php']);

        $this->assertEquals('set in TestListener', $jigsaw->getConfig('variable_a'));
    }

    public function test_multiple_event_listeners_are_fired_in_the_order_they_were_defined()
    {
        $this->app['events']->beforeBuild([TestListener::class, TestListenerTwo::class]);
        $jigsaw = $this->buildSite($this->setupSource(), ['variable_a' => 'set in config.php']);

        $this->assertEquals('set in SecondTestListener', $jigsaw->getConfig('variable_a'));
        $this->assertEquals('set in TestListener', $jigsaw->getConfig('variable_b'));
    }
}

class TestListener
{
    public function handle($jigsaw)
    {
        $jigsaw->setConfig('variable_a', 'set in TestListener');
        $jigsaw->setConfig('variable_b', 'set in TestListener');
    }
}

class TestListenerTwo
{
    public function handle($jigsaw)
    {
        $jigsaw->setConfig('variable_a', 'set in SecondTestListener');
    }
}
