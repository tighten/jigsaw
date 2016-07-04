<?php namespace TightenCo\Jigsaw\Handlers;

use TightenCo\Jigsaw\OutputFile;

class CollectionItemHandler
{
    private $collectionSettings;
    private $outputPathResolver;
    private $handlers;

    public function __construct($collectionSettings, $outputPathResolver, $handlers)
    {
        $this->collectionSettings = collect($collectionSettings);
        $this->outputPathResolver = $outputPathResolver;
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

        $processedFiles = $handler->handle($file, $data);
        $settings = $this->collectionSettings[$this->getCollectionName($file)];

        return collect($processedFiles)->map(function ($file) use ($settings) {
            $permalink = $settings['permalink']->__invoke($file->data());

            $path = implode(DIRECTORY_SEPARATOR, array_slice(explode(DIRECTORY_SEPARATOR, $permalink), 0, -1));
            $name = array_last(explode(DIRECTORY_SEPARATOR, $permalink));

            return new OutputFile($path, $name, $file->extension(), $file->contents(), $file->data());
        })->all();
    }
}
