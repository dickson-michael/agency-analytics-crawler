<?php

namespace MyCrawler;

/**
 * Basic implementation of a page scraper. Can extract a few useful tidbits about a page. You can,
 * and maybe even should, subclass this to add more functionality (Decorator style).
 */
class BasicPageScraper implements PageScraper
{
    /**
     * Scrape a web page for links, images, title, and plain-text content.
     *
     * @param string $html HTML contents of a web page to scrape.
     * @return mixed[] Free-form data scraped from the web page.
     */
    public function scrape(string $html) : array
    {
        $helper = new HtmlDocumentHelper($html);

        // Find all anchor nodes and extract the 'href' attribute.
        $links = $helper->extractAttribute('href', $helper->nodesByTag('a'));

        // Find all images nodes and extract the 'src' attribute.
        $images = $helper->extractAttribute('src', $helper->nodesByTag('img'));

        // Get the document <title>, properly at <html><head><title>
        $nodes = $helper->nodes('//title/text()');
        $title = array_map(function ($elm) {
            return trim($elm->nodeValue);
        }, $nodes);
        $title = array_shift($title);

        // Get the document <body>, properly at <html><body>
        $nodes = $helper->nodes('//body//*[not(self::script)][not(self::style)]/text()');
        $words = array_map(function ($elm) {
            return trim($elm->nodeValue);
        }, $nodes);
        $words = implode(' ', $words);

        return [
            'links' => $links,
            'images' => $images,
            'title' => $title,
            'text' => $words,
        ];
    }

    /**
     * Returns the list of links that were part of the page scrape.
     *
     * @param mixed[] $data The results of a page scrape, which should have identified some links.
     * @return string[] List of URLs we can continue to process as part of our crawl.
     */
    public function followOn(array $data) : array
    {
        return $data['links'];
    }
}
