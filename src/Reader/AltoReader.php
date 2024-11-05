<?php

namespace Alto\Reader;

use DOMDocument;
use DOMXPath;

class AltoReader
{
    public static function extractTextFromXml(string $xml): string
    {
        $document = new DOMDocument();
        $document->loadXML($xml);

        $rootElement = $document->documentElement;
        if ($rootElement->tagName !== 'alto') {
            return '';
        }

        $altoNamespaceUri = $rootElement->namespaceURI;
        if (!$altoNamespaceUri) {
            return '';
        }

        $xpath = new DOMXPath($document);
        $xpath->registerNamespace('alto', $altoNamespaceUri);

        $textBlocks = [];
        $textBlockNodes = $xpath->query('//alto:TextBlock');
        foreach ($textBlockNodes as $textBlockNode) {
            $textLines = [];
            $textLineNodes = $xpath->query('alto:TextLine', $textBlockNode);
            foreach ($textLineNodes as $textLineNode) {
                $strings = [];
                $stringNodes = $xpath->query('alto:String', $textLineNode);
                foreach ($stringNodes as $stringNode) {
                    $strings[] = $stringNode->getAttribute('CONTENT');
                }
                $textLines[] = implode(' ', $strings);
            }
            $textBlocks[] = implode("\n", $textLines);
        }

        $text = implode("\n\n", $textBlocks);

        return $text;
    }
}
