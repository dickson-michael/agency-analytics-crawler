<?php

namespace MyCrawler;

use DOMDocument;
use DOMNode;
use DOMXpath;

/**
 * Helper class for processing (suspected?) HTML documents.
 *
 * Basically some wrappers around the greatest DOM tool of all time, XPath.
 */
class HtmlDocumentHelper
{
    /** @var DOMDocument Structured copy of the HTML document. */
    private $document;

    /** @var DOMXpath XPath query tool for the DOMDocument. */
    private $xpath;

    /**
     * @param string $html Structured HTML contents of a web page.
     */
    public function __construct(string $html)
    {
        $this->document = @DOMDocument::loadHTML($html);
        $this->xpath = new DOMXpath($this->document);

        // TODO: register namespaces, etc.
    }

    /**
     * Performs an XPath query on the structured document to extract a list of matching nodes.
     *
     * @param string $xpath The XP an XPath query on the nodes.
     * @return DOMNode[] List of nodes that matched the query.
     */
    public function nodes(string $xpath) : array
    {
        return iterator_to_array($this->xpath->query($xpath));
    }

    /**
     * Extracts all elements from the document with a matching tag name.
     *
     * @param string $tag Tag name of the type of tags we are searching for.
     * @return DOMNode[] List of nodes in the document with the tag name.
     */
    public function nodesByTag(string $tag) : array
    {
        return $this->nodes('//' . $tag);
    }

    /**
     * Extract the attributes from a set of nodes matched by nodes query.
     *
     * @param string $attribute Name of the attribute to extract from the node list.
     * @param DOMNode[] $nodes List of nodes to extract attribute from.
     *
     * @return string[] List of node's attribute's values.
     */
    public function extractAttribute(string $attribute, array $nodes) : array
    {
        /** @var string[] */
        $data = array_map(function ($elm) use ($attribute) {
            $src = $elm->attributes->getNamedItem($attribute);
            return $src ? $src->nodeValue : null;
        }, $nodes);
        $data = array_filter($data);

        return $data;
    }
}
