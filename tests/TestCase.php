<?php

namespace Tests;

use PHPUnit\Framework\TestCase as BaseTestCase;

class TestCase extends BaseTestCase
{
    public $app;

    public function setUp()
    {
        parent::setUp();
        require('jigsaw-core.php');
        $this->app = $container;
    }
}
