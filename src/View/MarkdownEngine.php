<?php

namespace TightenCo\Jigsaw\View;

use Exception;
use Illuminate\Contracts\View\Engine as EngineInterface;
use Symfony\Component\Debug\Exception\FatalThrowableError;
use Throwable;

class MarkdownEngine implements EngineInterface
{
    private $parser;
    private $file;
    private $sourcePath;

    public function __construct($parser, $filesystem, $sourcePath)
    {
        $this->parser = $parser;
        $this->file = $filesystem;
        $this->sourcePath = $sourcePath;
    }

    public function get($path, array $data = [])
    {
        return $this->evaluateMarkdown($path);
    }

    protected function evaluateMarkdown($path)
    {
        try {
            $file = $this->file->get($path);

            if ($file) {
                return $this->parser->parseMarkdown($file);
            }
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
