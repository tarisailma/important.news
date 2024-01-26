<?php

namespace App\Libraries;

class GoogleSuggest
{
    public static function get($keyword = '')
    {
        $items = [];
        $url = 'http://google.com/complete/search?output=toolbar&q=' . $keyword;

        $ip = randomPick(option('proxies'));

        $response = fetch($url, [
            'headers' => [
                'User-Agent' =>  'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_16_7) AppleWebKit/' . rand(500, 604) . rand(20, 36) . ' (KHTML, like Gecko) Chrome/' . rand(80, 102) . '.0.0.0 Safari/' . rand(500, 604) . rand(20, 36),
                'Referer' => 'https://www.google.com',
            ],
            'proxy' => [
                'http'  => $ip,
                'https' => $ip,
            ]
        ]);

        if ($response->getStatusCode() == 200) {
            $html = $response->getBody();

            $dom = new \DOMDocument('4.01', 'UTF-8');
            @$dom->loadHTML($html);
            $xpath = new \DOMXPath($dom);

            $blocks = $xpath->query('//suggestion');
            if (count($blocks) == 0) {
                $blocks = [];
            }

            $items = [];
            foreach ($blocks as $blok) {
                $items[] = $blok->getAttribute('data');
            }
        }

        return json_encode($items);
    }
}
