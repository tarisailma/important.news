<?php

namespace App\Libraries;

use Symfony\Component\DomCrawler\Crawler;
use App\Libraries\Sentence;
use MaximeRenou\BingAI\BingAI;
use MaximeRenou\BingAI\Chat\Prompt;
use MaximeRenou\BingAI\Chat\Tone;

class Bing
{
    public static function Articles($options = [])
    {
        $sentences = [];
        $url = 'https://www.bing.com/search?q=' . $options['query'] . ' ' . option('append_query') . '&form=QBLH&sp=-1&lq=0&pq=' . $options['query'] . ' ' . option('append_query') . '&sc=0-26&qs=n&sk=&ghsh=0&ghacc=0&ghpl=';

        $ip = randomPick(option('proxies'));

        $response = fetch($url, [
            'headers' => [
                'User-Agent' =>  'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_16_7) AppleWebKit/' . rand(500, 604) . rand(20, 36) . ' (KHTML, like Gecko) Chrome/' . rand(80, 102) . '.0.0.0 Safari/' . rand(500, 604) . rand(20, 36),
                'Referer' => 'https://www.bing.com',
            ],
            'proxy' => [
                'http'  => $ip,
                'https' => $ip,
            ]
        ]);

        if ($response->getStatusCode() == 200) {
            $items = [];
            $html = $response->getBody()->getContents();
            $crawler = new Crawler($html);

            // remove all .news_dt nodes inside .content
            $crawler->filter('span.news_dt')->each(function (Crawler $crawler) {
                foreach ($crawler as $node) {
                    $node->parentNode->removeChild($node);
                }
            });

            foreach ($crawler->filter('li.b_algo') as $key => $item) {
                if (++$key > option('articles_limit')) break;

                $b_algo = new Crawler($item);

                try {
                    $item_data =  str_replace([' · ', ' …', '[...]'], ['', '.', ''],  $b_algo->filter("div.b_caption > p")->text());
                } catch (\Throwable $th) {
                    $item_data = '';
                }

                $items[] = $item_data;
            }

            shuffle($items);

            if (!empty($items)) {
                foreach ($items as $key => $desc) {
                    $new_sentences = Sentence::getSentence($desc);

                    if ($new_sentences) {
                        $sentences = array_merge($sentences, $new_sentences);
                    }
                }
            }
        }

        return $sentences;
    }

    public static function get($options = [])
    {
        $data = '';
        $cookies = getRandomLine('bing/cookies.txt');
        $ai = new BingAI($cookies);

        $cek_cookies = $ai->checkCookie();

        if ($cek_cookies == !true) {
            return $data;
        }

        try {
            $conversation = $ai->createChatConversation()
                ->withTone(Tone::Balanced);
        } catch (\Throwable $th) {
            return $data;
        }

        $query = $options['query'];

        $prompt =  spintax(strtr(option('search_prompt'), [
            '%keyword%' => $query
        ]));

        $prompt = new Prompt($prompt);
        list($text, $messages) = $conversation->ask($prompt->withoutCache());

        if ($text) {
            $data = badStringsRemover($text, 'bing') ?? '';
        }

        return $data;
    }

    public static function images($options = [])
    {
        $items = [];
        $url = 'https://www.bing.com/images/search?q=' . $options['query'] . ' ' . option('append_query') . '&qft=' . option('bing_images_filter') . '&count=' . $options['limit'] . '&form=IRFLTR&first=1';

        $ip = randomPick(option('proxies'));

        $response = fetch($url, [
            'headers' => [
                'User-Agent' =>  'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_16_7) AppleWebKit/' . rand(500, 604) . rand(20, 36) . ' (KHTML, like Gecko) Chrome/' . rand(80, 102) . '.0.0.0 Safari/' . rand(500, 604) . rand(20, 36),
                'Referer' => 'https://www.bing.com',
            ],
            'proxy' => [
                'http'  => $ip,
                'https' => $ip,
            ]
        ]);

        if ($response->getStatusCode() == 200) {
            $items = [];
            $html = $response->getBody()->getContents();
            $crawler = new Crawler($html);

            foreach ($crawler->filter('div.imgpt') as $key => $item) {
                if (++$key > $options['limit']) break;

                $imgpt = new Crawler($item);
                $json = @json_decode($imgpt->filter('a.iusc')->attr('m'));

                if (isset($json->t)) {
                    try {
                        $img_info = $imgpt->filter("div.img_info > span.nowrap")->text();
                        $size = substr($img_info, 0, strpos($img_info, " ·"));
                        $type = str_replace(' ', '', explode('·', $img_info)[1]);
                    } catch (\Throwable $th) {
                        $size = "0 x 0";
                        $type = "jpg";
                    }

                    $item_data['title'] = str_replace(['', '', ' ...'], '', $json->t);
                    $item_data['image'] = $json->murl;
                    $item_data['thumbnail'] = $json->turl;
                    $item_data['size'] =  $size;
                    $item_data['type'] =  $type;
                    $item_data['desc'] =  $json->desc;
                    $item_data['source'] = $json->purl;
                    $item_data['domain'] = parse_url($json->purl, PHP_URL_HOST);
                    $items[] = $item_data;
                }
            }

            if (option('shuffle_data') == true) {
                $items = shuffle_exclude($items, option('shuffle_index_exclude'));
            }
        }

        return $items;
    }
}
