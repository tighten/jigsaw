<?php

declare(strict_types=1);

namespace TightenCo\Jigsaw\Contracts;

use Illuminate\Support\Collection;
use TightenCo\Jigsaw\File\InputFile;
use TightenCo\Jigsaw\PageData;

interface ItemHandler extends Handler
{
    public function handle(InputFile $file, PageData $pageData): Collection;
}
