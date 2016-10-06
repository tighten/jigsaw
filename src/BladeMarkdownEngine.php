<?php namespace TightenCo\Jigsaw;

use Exception;
use Illuminate\View\Engines\EngineInterface;
use Symfony\Component\Debug\Exception\FatalThrowableError;
use Throwable;
use TightenCo\Jigsaw\Filesystem;

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
            return $this->markdown->parseMarkdown($content)->content;
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