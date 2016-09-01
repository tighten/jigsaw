<?php namespace TightenCo\Jigsaw;

class CollectionPathResolver
{
    private $outputPathResolver;
    private $viewRenderer;

    public function __construct($outputPathResolver, $viewRenderer)
    {
        $this->outputPathResolver = $outputPathResolver;
        $this->view = $viewRenderer;
    }

    public function link($permalink, $data)
    {
        return collect($data->extends)->map(function($bladeViewPath, $templateKey) use ($permalink, $data) {
            return $this->cleanOutputPath(
                $this->getPath($permalink, $data, $this->getExtension($bladeViewPath), $templateKey)
            );
        });
    }

    public function getExtension($bladeViewPath)
    {
        $extension = $this->view->getExtension($bladeViewPath);

        return collect(['php', 'html'])->contains($extension) ? '' : '.' . $extension;
    }

    private function getPath($permalink, $data, $extension, $templateKey = null)
    {
        $templateKeySuffix = $templateKey ? '/' . $templateKey : '';

        if ($templateKey && is_array($permalink)) {
            $permalink = array_get($permalink, $templateKey);
            $templateKeySuffix = '';

            if (! $permalink) {
                return;
            }
        }

        if (is_callable($permalink)) {
            $link = $this->cleanInputPath($permalink->__invoke($data));

            return $link ? $this->resolve($link . $templateKeySuffix . $extension) : '';
        }

        if (is_string($permalink) && $permalink) {
            $link =$this->parseShorthand($this->cleanInputPath($permalink), $data);

            return $link ? $this->resolve($link . $templateKeySuffix . $extension) : '';
        }

        return $this->getDefaultPermalink($data, $templateKey) . $templateKeySuffix . $extension;
    }

    private function getDefaultPermalink($data)
    {
        return str_slug($data['collection']) . '/' . str_slug($data['filename']);
    }

    private function parseShorthand($path, $data)
    {
        preg_match_all('/\{(.*?)\}/', $path, $bracketedParameters);

        if (count($bracketedParameters[0]) == 0) {
            return $path . '/' . str_slug($data['filename']);
        }

        $bracketedParametersReplaced =
            collect($bracketedParameters[0])->map(function($param) use ($data) {
                return ['token' => $param, 'value' => $this->getParameterValue($param, $data)];
            })->reduce(function ($carry, $param) use ($path) {
                return str_replace($param['token'], $param['value'], $carry);
            }, $path);

        return $bracketedParametersReplaced;
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

        return $slugSeparator ? str_slug($value, $slugSeparator) : $value;
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
