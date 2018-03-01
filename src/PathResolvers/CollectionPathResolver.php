<?php namespace TightenCo\Jigsaw\PathResolvers;

use TightenCo\Jigsaw\IterableObject;

class CollectionPathResolver
{
    private $outputPathResolver;
    private $viewRenderer;

    public function __construct($outputPathResolver, $viewRenderer)
    {
        $this->outputPathResolver = $outputPathResolver;
        $this->view = $viewRenderer;
    }

    public function link($path, $data)
    {
        return collect($data->extends)->map(function ($bladeViewPath, $templateKey) use ($path, $data) {
            return $this->cleanOutputPath(
                $this->getPath($path, $data, $this->getExtension($bladeViewPath), $templateKey)
            );
        });
    }

    public function getExtension($bladeViewPath)
    {
        $extension = $this->view->getExtension($bladeViewPath);

        return collect(['php', 'html'])->contains($extension) ? '' : '.' . $extension;
    }

    private function getPath($path, $data, $extension, $templateKey = null)
    {
        $templateKeySuffix = $templateKey ? '/' . $templateKey : '';

        if ($templateKey && $path instanceof IterableObject) {
            $path = $path->get($templateKey);
            $templateKeySuffix = '';

            if (! $path) {
                return;
            }
        }

        if (is_callable($path)) {
            $link = $this->cleanInputPath($path->__invoke($data));

            return $link ? $this->resolve($link . $templateKeySuffix . $extension) : '';
        }

        if (is_string($path) && $path) {
            $link = $this->parseShorthand($this->cleanInputPath($path), $data);

            return $link ? $this->resolve($link . $templateKeySuffix . $extension) : '';
        }

        return $this->getDefaultPath($data, $templateKey) . $templateKeySuffix . $extension;
    }

    private function getDefaultPath($data)
    {
        return $this->slug($data->getCollectionName()) . '/' . $this->slug($data->getFilename());
    }

    private function parseShorthand($path, $data)
    {
        preg_match_all('/\{(.*?)\}/', $path, $bracketedParameters);

        if (count($bracketedParameters[0]) == 0) {
            return $path . '/' . $this->slug($data->getFilename());
        }

        $bracketedParametersReplaced =
            collect($bracketedParameters[0])->map(function ($param) use ($data) {
                return ['token' => $param, 'value' => $this->getParameterValue($param, $data)];
            })->reduce(function ($carry, $param) use ($path) {
                return str_replace($param['token'], $param['value'], $carry);
            }, $path);

        return $bracketedParametersReplaced;
    }

    private function getParameterValue($param, $data)
    {
        list($param, $dateFormat) = explode('|', trim($param, '{}') . '|');
        $slugSeparator = ctype_alpha($param[0]) ? null : $param[0];

        if ($slugSeparator) {
            $param = ltrim($param, $param[0]);
        }

        $value = $this->filterInvalidCharacters(array_get($data, $param, $data->_meta->get($param)));

        if (! $value) {
            return '';
        }

        $value = $dateFormat ? $this->formatDate($value, $dateFormat) : $value;

        return $slugSeparator ? $this->slug($value, $slugSeparator) : $value;
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
        return '/' . trimPath($path);
    }

    private function resolve($path)
    {
        return $this->outputPathResolver->link(dirname($path), basename($path), 'html');
    }

    /**
     * This is identical to Laravel's built-in `str_slug()` helper,
     * except it preserves `.` characters.
     */
    private function slug($string, $separator = '-')
    {
        // Transliterate a UTF-8 value to ASCII
        $string = iconv('UTF-8', 'ASCII//TRANSLIT', $string);

        // Convert all dashes/underscores into separator
        $flip = $separator == '-' ? '_' : '-';
        $string = preg_replace('!['.preg_quote($flip).']+!u', $separator, $string);

        // Remove all characters that are not the separator, letters, numbers, whitespace, or dot
        $string = preg_replace('![^'.preg_quote($separator).'\pL\pN\s\.]+!u', '', mb_strtolower($string));

        // Replace all separator characters and whitespace by a single separator
        $string = preg_replace('!['.preg_quote($separator).'\s]+!u', $separator, $string);

        return trim($string, $separator);
    }

    /**
     * Filter characters that are invalid in URL, like ® and ™, allowing spaces
     */
    private function filterInvalidCharacters($value)
    {
        return is_string($value) ? preg_replace('/[^\x20-\x7E]/', '', $value) : $value;
    }
}
