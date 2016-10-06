<?php namespace TightenCo\Jigsaw;

use TightenCo\Jigsaw\IterableObject;
use TightenCo\Jigsaw\HelperFunctionTrait;

class ConfigSettings extends IterableObject
{
    use HelperFunctionTrait;

    public static function build($settings)
    {
        $config = new static($settings);
        return $config;
    }

    public function getHelper($name)
    {
        return $this->has('helpers') ? $this->helpers->{$name} : null;
    }

    private function missingHelperError($function_name)
    {
        return 'No helper function named "' . $function_name. '" was found in the file "config.php".';
    }
}
