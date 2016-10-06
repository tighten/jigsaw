<?php namespace TightenCo\Jigsaw;

use Exception;

trait HelperFunctionTrait
{
    public function __call($method, $args)
    {
        $helper = method_exists($this, 'getHelper') ? $this->getHelper($method) : null;

        return $helper ? $helper->__invoke($this, ...$args) : function() use ($method) {
            throw new Exception($this->missingHelperError($method));
        };
    }

    public function missingHelperError($helperName)
    {
        return 'No helper function named "' . $helperName. '" was found.';
    }
}
