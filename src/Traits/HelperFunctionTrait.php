<?php namespace TightenCo\Jigsaw\Traits;

use Exception;

trait HelperFunctionTrait
{
    public function __call($method, $args)
    {
        $helper = $this->get($method);

        if (! $helper && starts_with($method, 'get')) {
            return $this->_meta->get(camel_case(substr($method, 3)), function () use ($method) {
                throw new Exception($this->missingHelperError($method));
            });
        }

        if (is_callable($helper)) {
            return $helper->__invoke($this, ...$args);
        } else {
            throw new Exception($this->missingHelperError($method));
        }
    }

    public function missingHelperError($method)
    {
        return 'No function named "' . $method. '" was found.';
    }
}
