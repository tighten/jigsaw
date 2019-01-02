<?php

declare(strict_types=1);

namespace TightenCo\Jigsaw\Contracts;

use TightenCo\Jigsaw\File\InputFile;

interface CollectionItemHandler extends Handler
{
    public function getItemVariables(InputFile $file): array;

    public function getItemContent(InputFile $file): callable;
}
