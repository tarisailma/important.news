<?php

namespace App\Libraries;

class OpenAI
{
    public static function get($options = [])
    {
        $data = '';
        $query = $options['query'];

        $openai_api = getRandomLine('openai/api-keys.txt');
        $client = \OpenAI::client($openai_api);

        $prompt =  spintax(strtr(option('search_prompt'), [
            '%keyword%' => $query
        ]));

        try {
            $result = $client->chat()->create([
                'model' => 'gpt-3.5-turbo',
                'messages' => [[
                    'role' => 'user',
                    'content' => $prompt,
                ]],
                'temperature' => 1,
                'max_tokens' => option('openai_max_token'),
                'top_p' => 1,
                'frequency_penalty' => 0,
                'presence_penalty' => 0

            ]);

            $data = $result['choices'][0]['message']['content'] ?? '';
        } catch (\Throwable $th) {
            //
        }

        return $data;
    }
}
