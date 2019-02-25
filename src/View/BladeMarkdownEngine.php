<?php

namespace TightenCo\Jigsaw\View;

use Exception;
use Illuminate\Contracts\View\Engine as EngineInterface;
use Symfony\Component\Debug\Exception\FatalThrowableError;
use Throwable;

class BladeMarkdownEngine implements EngineInterface
{
    private $blade;
    private $markdown;

    public function __construct($compilerEngine, $markdown)
    {
        $this->blade = $compilerEngine;
        $this->markdown = $markdown;
    }

    public function get($path, array $data = [])
    {
        $content = $this->evaluateBlade($path, $data);

        return $this->evaluateMarkdown($content);
    }

    protected function evaluateBlade($path, $data)
    {
        try {
            return $this->blade->get($path, $data);
        } catch (Exception $e) {
            $this->handleViewException($e);
        } catch (Throwable $e) {
            $this->handleViewException(new FatalThrowableError($e));
        }
    }

    protected function evaluateMarkdown($content)
    {
        try {
            return $this->markdown->parseMarkdown($content);
        } catch (Exception $e) {
            $this->handleViewException($e);
        } catch (Throwable $e) {
            $this->handleViewException(new FatalThrowableError($e));
        }
    }

    protected function handleViewException(Exception $e)
    {
        throw $e;
    }
}
