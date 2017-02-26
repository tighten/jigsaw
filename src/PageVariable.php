<?php namespace TightenCo\Jigsaw;

class PageVariable extends IterableObject
{
    public function addVariables($variables)
    {
        $this->items = collect($this->items)->merge($this->makeIterable($variables))->all();
    }

    public function __call($method, $args)
    {
        $helper = $this->get($method);

        if (! $helper && starts_with($method, 'get')) {
            return $this->_meta->get(camel_case(substr($method, 3)), function () use ($method) {
                throw new \Exception($this->missingHelperError($method));
            });
        }

        if (is_callable($helper)) {
            return $helper->__invoke($this, ...$args);
        } else {
            throw new \Exception($this->missingHelperError($method));
        }
    }

    protected function missingHelperError($functionName)
    {
        return 'No function named "' . $functionName . '" was found in the file "config.php".';
    }
}
