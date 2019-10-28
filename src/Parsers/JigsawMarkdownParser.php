<?php

namespace TightenCo\Jigsaw\Parsers;

use DOMDocument;
use DOMElement;
use ParsedownExtra;

class JigsawMarkdownParser extends ParsedownExtra
{
    // This extension of ParsedownExtra is intended to deal with parsing errors
    // that arise from single-line markup that appears in markdown, e.g.:
    //
    // <h1>Some heading</h1><p>Some content</p>
    //
    // Without this fix, only the first node (<h1> in this example) is maintained.
    //
    // This fix comes largely from Adam Mitchell in this PR to ParsedownExtra:
    // https://github.com/erusev/parsedown-extra/pull/58
    // ...which has been around since 2015, but has not been merged into the package.

    protected $document;

    protected function blockMarkupComplete($Block)
    {
        if (! isset($Block['void'])) {
            $Block['markup'] = $this->processTags($Block['markup']);
        }

        return $Block;
    }

    protected function processTags($elementMarkup)
    {
        // http://stackoverflow.com/q/1148928/200145
        libxml_use_internal_errors(true);
        $DOMDocument = new DOMDocument();

        // http://stackoverflow.com/q/11309194/200145
        $elementMarkup = mb_convert_encoding($elementMarkup, 'HTML-ENTITIES', 'UTF-8');

        // http://stackoverflow.com/q/4879946/200145
        $DOMDocument->loadHTML($elementMarkup, LIBXML_HTML_NODEFDTD);

        // This will add wrapping <body> and <html> tags, which we need to ignore.
        // Trying to _remove_ them, instead, will break single-line, multi-node markup
        $dom = $DOMDocument->firstChild->firstChild;
        $markup = '';

        if ($dom->hasChildNodes()) {
            foreach ($dom->childNodes as $childElement) {
                if ($childElement->nodeName !== 'html' && $childElement->nodeName !== 'body') {
                    $markup .= $this->processTag($childElement, $DOMDocument);
                }
            }
        }

        return $markup;
    }

    protected function processTag($element, $document = null)
    {
        $elementText = '';

        if ($element instanceof DOMElement && $element->getAttribute('markdown') === '1') {
            if ($element->hasChildNodes()) {
                foreach ($element->childNodes as $node) {
                    $elementText .= $document->saveHTML($node);
                }
            } else {
                $elementText = $document->saveHTML($element);
            }

            $element->removeAttribute('markdown');
            $elementText = "\n" . $this->text($elementText) . "\n";
        } else {
            if ($element->hasChildNodes()) {
                foreach ($element->childNodes as $node) {
                    $elementText .= $this->processText($node, $document);
                }
            } else {
                $elementText =  $this->processText($element, $document);
            }
        }

        // because we don't want markup to get encoded
        $element->nodeValue = 'placeholder\x1A';

        $markup = $document->saveHTML($element);
        $markup = str_replace('placeholder\x1A', $elementText, $markup);

        return $markup;
    }

    protected function processText($element, $document)
    {
        $nodeMarkup = $document->saveHTML($element);
        $text = '';

        if ($element instanceof DOMElement
            && ! in_array($element->nodeName, $this->textLevelElements)
            && ! in_array($element->nodeName, $this->voidElements)
        ) {
            if ($element->hasChildNodes()) {
                $text = $this->processTags($nodeMarkup);
            } else {
                $text = $nodeMarkup;
            }
        } else {
            $text = $nodeMarkup;
        }

        return $text;
    }
}
