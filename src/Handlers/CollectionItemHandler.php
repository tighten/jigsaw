<?php

declare(strict_types=1);

namespace TightenCo\Jigsaw\Handlers;

use Illuminate\Support\Collection;
use TightenCo\Jigsaw\File\OutputFile;

class CollectionItemHandler
{
    private $config;
    private $handlers;

    public function __construct($config, $handlers)
    {
        $this->config = $config;
        $this->handlers = collect($handlers);
    }

    public function shouldHandle($file): bool
    {
        return $this->isInCollectionDirectory($file)
            && ! starts_with($file->getFilename(), ['.', '_']);
    }

    private function isInCollectionDirectory($file): bool
    {
        $base = $file->topLevelDirectory();

        return starts_with($base, '_') && $this->hasCollectionNamed($this->getCollectionName($file));
    }

    private function hasCollectionNamed($candidate): bool
    {
        return array_get($this->config, 'collections.' . $candidate) !== null;
    }

    private function getCollectionName($file): string
    {
        return substr($file->topLevelDirectory(), 1);
    }

    public function handle($file, $pageData): Collection
    {
        $handler = $this->handlers->first(function ($handler) use ($file): bool {
            return $handler->shouldHandle($file);
        });
        $pageData->setPageVariableToCollectionItem($this->getCollectionName($file), $file->getFilenameWithoutExtension());

        return $handler->handleCollectionItem($file, $pageData)
            ->map(function ($outputFile, $templateToExtend): OutputFile {
                if ($templateToExtend) {
                    $outputFile->data()->setExtending($templateToExtend);
                }

                $path = $outputFile->data()->page->getPath();

                return new OutputFile(
                    dirname($path),
                    basename($path, '.' . $outputFile->extension()),
                    $outputFile->extension(),
                    $outputFile->contents(),
                    $outputFile->data()
                );
            })->values();
    }
}
