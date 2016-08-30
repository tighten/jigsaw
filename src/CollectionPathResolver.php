<?php namespace TightenCo\Jigsaw;

class CollectionPathResolver
{
    private $outputPathResolver;

    public function __construct($outputPathResolver)
    {
        $this->outputPathResolver = $outputPathResolver;
    }

    public function link($permalink, $data)
    {
        return $this->cleanOutputPath($this->getPath($permalink, $data));
    }

    private function getPath($permalink, $data)
    {
        if (is_callable($permalink)) {
            return $this->resolve($this->cleanInputPath($permalink->__invoke($data)));
        }

        if (is_string($permalink) && $permalink) {
            return $this->parseShorthand($this->cleanInputPath($permalink), $data);
        }

        return $this->getDefaultPermalink($data);
    }

    private function getDefaultPermalink($data)
    {
        return slugify($data['filename']);
    }

    private function parseShorthand($path, $data)
    {
        preg_match_all('/\{(.*?)\}/', $path, $bracketedParameters);

        if (count($bracketedParameters[0]) == 0) {
            return $path . '/' . $this->getDefaultPermalink($data);
        }

        $bracketedParametersReplaced =
            collect($bracketedParameters[0])->map(function($param) use ($data) {
                return ['token' => $param, 'value' => $this->getParameterValue($param, $data)];
            })->reduce(function ($carry, $param) use ($path) {
                return str_replace($param['token'], $param['value'], $carry);
            }, $path);

        return $this->resolve($bracketedParametersReplaced);
    }

    private function getParameterValue($param, $data) {
        list($param, $dateFormat) = explode('|', trim($param, '{}') . '|');
        $slugSeparator = ctype_alpha($param[0]) ? null : $param[0];

        if ($slugSeparator) {
            $param = ltrim($param, $param[0]);
        }

        if (! isset($data[$param])) {
            return '';
        }

        $value = $dateFormat ? $this->formatDate($data[$param], $dateFormat) : $data[$param];

        return $slugSeparator ? slugify($value, $slugSeparator) : $value;
    }

    private function formatDate($date, $format)
    {
        if (is_string($date)) {
            return strtotime($date) ? date($format, strtotime($date)) : '';
        }

        return date($format, $date);
    }

    private function cleanInputPath($path)
    {
        return $this->ensureSlashAtBeginningOnly($path);
    }

    private function cleanOutputPath($path)
    {
        $removeDoubleSlashes = preg_replace('/\/\/+/', '/', $path);

        return $this->ensureSlashAtBeginningOnly($removeDoubleSlashes);
    }

    private function ensureSlashAtBeginningOnly($path)
    {
        return '/' . trim($path, '/.');
    }

    private function resolve($path)
    {
        return $this->outputPathResolver->link(dirname($path), basename($path), 'html');
    }
}
