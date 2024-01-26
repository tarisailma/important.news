<?php

namespace App\Libraries;

use Carbon\Carbon;
use Pj8912\PhpBardApi\Bard;

class BardAI
{
    public $data;
    public $dataContent;
    public $niche;

    public static function get($options = [])
    {
        $data = '';
        $cookies = getRandomLine('bard/cookies.txt');
        $api_key = explode(",", $cookies);
        $_ENV['BARD_API_KEY_X'] = $api_key[0] ?? "";
        $_ENV['BARD_API_KEY_Y'] = $api_key[1] ?? "";

        $query = $options['query'];
        $prompt =  spintax(strtr(option('search_prompt'), [
            '%keyword%' => $query
        ]));

        try {
            $ai = new Bard(option('connection_timeout'));
            $result = $ai->get_answer($prompt);
            $text = badStringsRemover($result["content"]);
        } catch (\Throwable $th) {
            return $data;
        }

        if ($text) {
            $data = $text;
        }

        return $data;
    }
}
