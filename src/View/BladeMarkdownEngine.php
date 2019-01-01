<?php

declare(strict_types=1);

namespace TightenCo\Jigsaw\View;

use Exception;
use Illuminate\Contracts\View\Engine as EngineInterface;
use Illuminate\View\Engines\CompilerEngine;
use Symfony\Component\Debug\Exception\FatalThrowableError;
use Throwable;
use TightenCo\Jigsaw\Parsers\FrontMatterParser;

class BladeMarkdownEngine implements EngineInterface
{
    /** @var CompilerEngine */
    private $blade;

    /** @var FrontMatterParser */
    private $markdown;

    public function __construct($compilerEngine, $markdown)
    {
        $this->blade = $compilerEngine;
        $this->markdown = $markdown;
    }

    public function get($path, array $data = []): string
    {
        $content = $this->evaluateBlade($path, $data);

        return $this->evaluateMarkdown($content);
    }

    protected function evaluateBlade(string $path, array $data): string
    {
        try {
            return $this->blade->get($path, $data);
        } catch (Exception $e) {
            $this->handleViewException($e);
        } catch (Throwable $e) {
            $this->handleViewException(new FatalThrowableError($e));
        }
    }

    protected function evaluateMarkdown(string $content): string
    {
        try {
            return $this->markdown->parseMarkdown($content);
        } catch (Exception $e) {
            $this->handleViewException($e);
        } catch (Throwable $e) {
            $this->handleViewException(new FatalThrowableError($e));
        }
    }

    protected function handleViewException(Exception $e): void
    {
        throw $e;
    }
}
