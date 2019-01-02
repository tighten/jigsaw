<?php

declare(strict_types=1);

namespace TightenCo\Jigsaw\Contracts;

use TightenCo\Jigsaw\File\InputFile;

interface Handler
{
    public function shouldHandle(InputFile $file): bool;
}
