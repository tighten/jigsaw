<?php

declare(strict_types=1);

namespace TightenCo\Jigsaw\Handlers;

use Illuminate\Support\Collection;
use TightenCo\Jigsaw\Contracts\ItemHandler;
use TightenCo\Jigsaw\File\InputFile;
use TightenCo\Jigsaw\File\OutputFile;
use TightenCo\Jigsaw\PageData;

class CollectionItemHandler implements ItemHandler
{
    /** @var Collection */
    private $config;

    /** @var ItemHandler[]|Collection */
    private $handlers;

    /**
     * @param ItemHandler[] $handlers
     */
    public function __construct(Collection $config, array $handlers)
    {
        $this->config = $config;
        $this->handlers = collect($handlers);
    }

    public function shouldHandle(InputFile $file): bool
    {
        return $this->isInCollectionDirectory($file)
            && ! starts_with($file->getFilename(), ['.', '_']);
    }

    private function isInCollectionDirectory(InputFile $file): bool
    {
        $base = $file->topLevelDirectory();

        return starts_with($base, '_') && $this->hasCollectionNamed($this->getCollectionName($file));
    }

    private function hasCollectionNamed(string $candidate): bool
    {
        return array_get($this->config, 'collections.' . $candidate) !== null;
    }

    private function getCollectionName(InputFile $file): string
    {
        return substr($file->topLevelDirectory(), 1);
    }

    public function handle(InputFile $file, PageData $pageData): Collection
    {
        $handler = $this->handlers->first(function (ItemHandler $handler) use ($file): bool {
            return $handler->shouldHandle($file);
        });
        $pageData->setPageVariableToCollectionItem($this->getCollectionName($file), $file->getFilenameWithoutExtension());

        return $handler->handleCollectionItem($file, $pageData)
            ->map(function (OutputFile $outputFile, string $templateToExtend): OutputFile {
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
