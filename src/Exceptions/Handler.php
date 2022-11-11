<?php

namespace TightenCo\Jigsaw\Exceptions;

use Illuminate\Contracts\Debug\ExceptionHandler;
use Throwable;

class Handler implements ExceptionHandler
{
    /**
     * {@inheritdoc}
     */
    public function report(Throwable $e)
    {
        //
    }

    /**
     * {@inheritdoc}
     */
    public function shouldReport(Throwable $e)
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function render($request, Throwable $e)
    {
        //
    }

    /**
     * {@inheritdoc}
     */
    public function renderForConsole($output, Throwable $e)
    {
        //
    }
}
