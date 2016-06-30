<?php namespace TightenCo\Jigsaw;

use Illuminate\Contracts\View\Factory;
use Illuminate\Support\Str;
use TightenCo\Jigsaw\Filesystem;

class Jigsaw
{
    private $dataLoader;
    private $siteBuilderGenerator;

    public function __construct($dataLoader, $siteBuilderGenerator)
    {
        $this->dataLoader = $dataLoader;
        $this->siteBuilderGenerator = $siteBuilderGenerator;
    }

    public function build($source, $dest, $env, $options = [])
    {
        $siteData = $this->dataLoader->load($source, $env, $options);
        $this->siteBuilderGenerator->__invoke($source, $dest, $siteData, $options)->build();
    }
}
