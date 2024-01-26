<?php

namespace App\Libraries;

class Sentence
{
    //thanks to buchin
    //https://github.com/buchin/sentence-finder/blob/master/src/SentenceFinder.php

    public static function getSentence($result)
    {

        $extracted = preg_split("/(?<=[.?!;:])\s+/", $result, -1, PREG_SPLIT_NO_EMPTY);

        $new_sentences = [];
        foreach ($extracted as $sentence) {
            $sentence = preg_replace("/\.+/", ". ", trim($sentence));
            $sentence = str_replace(" .", ".", $sentence);
            $pos = self::str_contains($sentence, ["-", "–", "http"]);
            $word_count = count(explode(" ", $sentence));

            if ($pos === false && $word_count > 4) {
                $sentence = str_replace(['"'], "", $sentence);
                $sentence = self::mb_ucfirst(mb_strtolower($sentence));
                $sentence = strip_tags($sentence);
                $new_sentences[] = $sentence;
            }
        }
        return $new_sentences;
    }

    public static function str_contains($haystack, $needles)
    {
        foreach ($needles as $needle) {
            if (stripos($haystack, $needle) !== false) {
                return true;
            }
        }

        return false;
    }

    public static function mb_ucfirst(string $str, string $encoding = null): string
    {
        if (is_null($encoding)) {
            $encoding = mb_internal_encoding();
        }

        return mb_strtoupper(mb_substr($str, 0, 1, $encoding), $encoding) .
            mb_substr($str, 1, null, $encoding);
    }
}
