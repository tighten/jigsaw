<?php

namespace TightenCo\Jigsaw\Builders;

use Illuminate\Support\Str;

class PlainMarkdownBuilder
{
    public function build($content, $title = null, array $options = [])
    {
        $titleAsHeadingOne = $options['title_as_h1'] ?? true;
        $tableOfContents = $options['table_of_contents'] ?? true;

        $headings = $tableOfContents ? $this->extractHeadings($content) : [];

        if ($headings) {
            $content = $this->addAnchors($content, $headings);
        }

        $parts = [];

        if ($titleAsHeadingOne && $title) {
            $parts[] = "# {$title}";
        }

        if ($headings) {
            $parts[] = implode("\n", array_map(function ($heading) {
                return "- [{$heading['text']}](#{$heading['slug']})";
            }, $headings));
        }

        $parts[] = $content;

        return implode("\n\n", $parts) . "\n";
    }

    private function extractHeadings($content)
    {
        preg_match_all('/^(#{2,6})\s+(.+)$/m', $content, $matches, PREG_SET_ORDER);

        return array_map(function ($match) {
            return [
                'level' => strlen($match[1]),
                'text' => trim($match[2]),
                'slug' => Str::slug($match[2]),
            ];
        }, $matches);
    }

    private function addAnchors($content, $headings)
    {
        foreach ($headings as $heading) {
            $pattern = '/^(#{' . $heading['level'] . '})\s+(' . preg_quote($heading['text'], '/') . ')$/m';
            $replacement = '<a name="' . $heading['slug'] . '"></a>' . "\n" . '$1 $2';
            $content = preg_replace($pattern, $replacement, $content, 1);
        }

        return $content;
    }
}
