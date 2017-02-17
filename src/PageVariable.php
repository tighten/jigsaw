<?php namespace TightenCo\Jigsaw;

use TightenCo\Jigsaw\IterableObject;
use TightenCo\Jigsaw\Traits\HelperFunctionTrait;

class PageVariable extends IterableObject
{
    use HelperFunctionTrait;

    public function addVariables($variables)
    {
        $this->items = collect($this->items)->merge($variables)->all();
    }

    protected function missingHelperError($functionName)
    {
        return 'No function named "' . $functionName . '" was found in the file "config.php".';
    }
}
