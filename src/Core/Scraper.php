<?php

namespace JackCrawley\TrustpilotScraper\Core;

class Scraper
{
    /** @var resource */
    private $context;

    /** @return void */
    public function __construct()
    {
        $this->context = stream_context_create([
            'http' => [
                'header' => "User-Agent:MyAgent/1.0\r\n"
            ]
        ]);
    }

    /**
     * Parse HTML into a DOMDocument object.
     * 
     * @param  string $html
     * @return \DOMDocument
     */
    private function load_markup($html)
    {
        $document = new \DOMDocument();
        @$document->loadHTML($html);

        return $document;
    }

    /**
     * Run a query selection on the dom document.
     * 
     * @param  \DOMDocument $document
     * @param  string $query_string
     * @return \DOMElement[]
     */
    private function query_selector($document, $query_string)
    {
        $path = new \DOMXPath($document);
        $results = $path->query($query_string);

        $array = [];

        for ($i = 0; $i < $results->length; $i++) {
            $array[] = $results->item($i);
        }

        return $array;
    }

    /**
     * Return an array of filtered results.
     * 
     * @return void
     */
    public function search($query)
    {
        $data = @file_get_contents('https://uk.trustpilot.com/search?query=' . urlencode($query), false, $this->context) or throw new Exception('Connection failed!');
        $document = $this->load_markup($data);

        $results = $this->query_selector($document, "//*[@class='item clearfix']");

        $response = [];

        foreach ($results as $result) {
            $title_node = $result->childNodes->item(1);
            $title_node_content = $title_node->textContent;
            $title = explode('|', $title_node_content);
            list($title, $website) = array_map(fn ($value) => trim($value), $title);

            $url_node = $title_node->childNodes->item(1);
            $url = $url_node->getAttribute('href');

            $review = $result->childNodes->item(5);
            $review_value = explode(' ', $review->textContent)[0] ?? false;

            $trust_score_node = $result->childNodes->item(9);
            $trust_score_node_content = substr($trust_score_node->textContent, 11);

            $array = [
                'title'         => $title,
                'website'       => $website,
                'reviews'       => $review_value,
                'trust_score'   => $trust_score_node_content,
                'url'           => 'https://uk.trustpilot.com' . $url
            ];

            array_push($response, $array);
        }

        return $response;
    }
}