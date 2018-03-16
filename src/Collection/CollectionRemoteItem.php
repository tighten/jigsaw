<?php namespace TightenCo\Jigsaw\Collection;

use Symfony\Component\Yaml\Yaml;
use TightenCo\Jigsaw\Parsers\FrontMatterParser;

class CollectionRemoteItem
{
    private $item;
    private $index;
    private $prefix;

    public function __construct($item, $index = 0, $collectionName = null)
    {
        $this->item = $item;
        $this->index = $index;
        $this->prefix = $collectionName . '_';
    }

    public function getContent()
    {
        return is_array($this->item) ?
            $this->getHeader() . array_get($this->item, 'content') :
            $this->item;
    }

    public function getFilename()
    {
        return array_get($this->item, 'filename', $this->prefix . ($this->index + 1)) . '.md';
    }

    protected function getHeader()
    {
        $variables = collect($this->item)->except('content')->toArray();

        return count($variables) ? "---\n" . Yaml::dump($variables) . "---\n" : null;
    }
}
