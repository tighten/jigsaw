<?php namespace TightenCo\Jigsaw\CollectionItemHandlers;

class JsonCollectionItemHandler
{
    public function shouldHandle($file)
    {
        return $file->getExtension() == 'json';
    }

    public function getItemVariables($file)
    {
        $variables = json_decode($file->getContents(), true);
        unset($variables['content']);

        return $variables;
    }

    public function getItemContent($file)
    {
        return array_get(json_decode($file->getContents(), true), 'content');
    }
}
