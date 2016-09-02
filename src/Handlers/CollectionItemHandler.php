<?php namespace TightenCo\Jigsaw\Handlers;

use TightenCo\Jigsaw\OutputFile;
use TightenCo\Jigsaw\ViewData;

class CollectionItemHandler
{
    private $collectionSettings;
    private $handlers;

    public function __construct($collectionSettings, $handlers)
    {
        $this->collectionSettings = collect($collectionSettings);
        $this->handlers = collect($handlers);
    }

    public function shouldHandle($file)
    {
        return $this->isInCollectionDirectory($file);
    }

    private function isInCollectionDirectory($file)
    {
        $base = $file->topLevelDirectory();
        return starts_with($base, '_') && $this->hasCollectionNamed($this->getCollectionName($file));
    }

    private function hasCollectionNamed($candidate)
    {
        return $this->collectionSettings->has($candidate);
    }

    private function getCollectionName($file)
    {
        return substr($file->topLevelDirectory(), 1);
    }

    public function handle($file, $data)
    {
        $handler = $this->handlers->first(function ($_, $handler) use ($file) {
            return $handler->shouldHandle($file);
        });

        $viewData = ViewData::withCollectionItem($data, $this->getCollectionName($file), $file->getFilenameWithoutExtension());
        $handledFiles = $handler->handleCollectionItem($file, $viewData);

        return $handledFiles->map(function ($file, $templateKey) {
            $link = $templateKey ? $file->data()->link->get($templateKey) : (string) $file->data()->link;

            return new OutputFile(
                dirname($link),
                basename($link, '.' . $file->extension()),
                $file->extension(),
                $file->contents(),
                $file->data()
            );
        })->values();
    }
}
