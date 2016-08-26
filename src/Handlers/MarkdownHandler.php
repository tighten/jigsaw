<?php namespace TightenCo\Jigsaw\Handlers;

use Mni\FrontYAML\Parser;
use Illuminate\Contracts\View\Factory;
use TightenCo\Jigsaw\OutputFile;
use TightenCo\Jigsaw\ViewData;

class MarkdownHandler
{
    private $temporaryFilesystem;
    private $viewFactory;
    private $parser;

    public function __construct($temporaryFilesystem, Factory $viewFactory, $parser = null)
    {
        $this->temporaryFilesystem = $temporaryFilesystem;
        $this->viewFactory = $viewFactory;
        $this->parser = $parser ?: new Parser;
    }

    public function shouldHandle($file)
    {
        return in_array($file->getExtension(), ['markdown', 'md']);
    }

    public function handle($file, $data)
    {
        if (! $file->hasBeenParsed()) {
        $document = $this->parseFile($file);
            $data = new ViewData(
                $data->put('section', 'content')
                ->merge($document->frontMatter)
                ->put('content', $document->content)
            );
        }

        if (! $data->extends) {
            return;
        }

        return [
            new OutputFile(
                $file->getRelativePath(),
                $file->getFilenameWithoutExtension(),
                'html',
                $this->render($data),
                $data
            )
        ];
    }

    private function render($viewData)
    {
        return $this->temporaryFilesystem->put($this->compileToBlade($viewData), function ($path) use ($viewData) {
            return $this->viewFactory->file($path, ['jigsaw' => $viewData])->render();
        }, '.blade.php');
    }

    private function parseFile($file)
    {
        return $this->parser->parse($file->getContents());
    }

    private function compileToBlade($data)
    {
        return collect([
            sprintf("@extends('%s')", $data->extends),
            sprintf("@section('%s')", $data->section),
            '{!! $jigsaw->content !!}',
            '@endsection',
        ])->implode("\n");
    }
}
