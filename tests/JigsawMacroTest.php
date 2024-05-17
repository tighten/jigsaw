<?php

namespace Tests;

use PHPUnit\Framework\Attributes\Test;
use TightenCo\Jigsaw\Jigsaw;

class JigsawMacroTest extends TestCase
{
    #[Test]
    public function jigsaw_macro_function_calls_successfully()
    {
        Jigsaw::macro('getNameMacro', function () {
            return 'Reed';
        });

        $this->assertSame('Reed', Jigsaw::getNameMacro());
    }

    #[Test]
    public function jigsaw_mixin_function_calls_successfully()
    {
        Jigsaw::mixin(new JigsawMixinTestClass);

        $this->assertSame('Reed', Jigsaw::getNameMixin());
    }
}

class JigsawMixinTestClass
{
    public function getNameMixin()
    {
        return function () {
            return 'Reed';
        };
    }
}
