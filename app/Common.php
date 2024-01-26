<?php

use App\Libraries\Youtube;
use App\Libraries\iTunes;
use App\Libraries\RandomUserAgent;
use Carbon\Carbon;
use Ramsey\Uuid\Uuid;

function option($key = '')
{
    $ciCache = \Config\Services::cache();
    $option = require APPPATH . 'Config/Sites.php';

    if (empty($key)) {
        return $option;
    }

    if ($key == 'site_name') {
        $cacheName = '__site_name-' . site_domain();
        if (!$site_name = $ciCache->get($cacheName)) {
            $site_name = spintax($option['site_name']);
            $ciCache->save($cacheName, $site_name, 99999999999);
        }

        return $site_name;
    }

    if ($key == 'site_tagline') {
        $cacheName = '__site_tagline-' . site_domain();

        if (!$site_tagline = $ciCache->get($cacheName)) {
            $site_tagline = spintax($option['site_tagline']);
            $ciCache->save($cacheName, $site_tagline, 99999999999);
        }

        return $site_tagline;
    }

    // if ($key == 'theme') {
    //     $cacheName = '__theme-' . site_domain();

    //     if (!$site_theme = $ciCache->get($cacheName)) {
    //         $site_theme = random_pick($option['theme']);
    //         $ciCache->save($cacheName, $site_theme, 99999999999);
    //     }

    //     return $site_theme;
    // }

    return isset($option[$key]) ? $option[$key] : null;
}

function photon_resize($url = '', $width = '', $height = '', $server = '0')
{
    return 'https://i' . $server . '.wp.com/' . preg_replace('/(^\w+:|^)\/\//', '', $url) . '?resize=' . $width . ',' . $height;
}

function make_slug($str, $delimiter = '-', $options = [])
{
    $str = strtr($str, [
        '&amp;' => '&',
        '&quot;' => '"',
        '&#039;' => "'",
        '&#39;' => "'",
        "n't" => 'nt'
    ]);
    $str = urldecode(html_entity_decode($str));
    $str = mb_convert_encoding((string) $str, 'UTF-8', mb_list_encodings());
    $defaults = [
        'delimiter'     => $delimiter,
        'limit'         => null,
        'lowercase'     => true,
        'replacements'  => [],
        'transliterate' => false,
    ];
    $options = array_merge($defaults, $options);
    $chars_map = [
        // Latin
        'ÃƒÂ€' => 'A', 'ÃƒÂ' => 'A', 'ÃƒÂ‚' => 'A', 'ÃƒÂƒ' => 'A', 'ÃƒÂ„' => 'A', 'ÃƒÂ…' => 'A', 'ÃƒÂ†' => 'AE', 'ÃƒÂ‡' => 'C',
        'ÃƒÂˆ' => 'E', 'ÃƒÂ‰' => 'E', 'ÃƒÂŠ' => 'E', 'ÃƒÂ‹' => 'E', 'ÃƒÂŒ' => 'I', 'ÃƒÂ' => 'I', 'ÃƒÂŽ' => 'I', 'ÃƒÂ' => 'I',
        'ÃƒÂ' => 'D', 'ÃƒÂ‘' => 'N', 'ÃƒÂ’' => 'O', 'ÃƒÂ“' => 'O', 'ÃƒÂ”' => 'O', 'ÃƒÂ•' => 'O', 'ÃƒÂ–' => 'O', 'Ã…Â' => 'O',
        'ÃƒÂ˜' => 'O', 'ÃƒÂ™' => 'U', 'ÃƒÂš' => 'U', 'ÃƒÂ›' => 'U', 'ÃƒÂœ' => 'U', 'Ã…Â°' => 'U', 'ÃƒÂ' => 'Y', 'ÃƒÂž' => 'TH',
        'ÃƒÂŸ' => 'ss',
        'Ãƒ ' => 'a', 'ÃƒÂ¡' => 'a', 'ÃƒÂ¢' => 'a', 'ÃƒÂ£' => 'a', 'ÃƒÂ¤' => 'a', 'ÃƒÂ¥' => 'a', 'ÃƒÂ¦' => 'ae', 'ÃƒÂ§' => 'c',
        'ÃƒÂ¨' => 'e', 'ÃƒÂ©' => 'e', 'ÃƒÂª' => 'e', 'ÃƒÂ«' => 'e', 'ÃƒÂ¬' => 'i', 'ÃƒÂ­' => 'i', 'ÃƒÂ®' => 'i', 'ÃƒÂ¯' => 'i',
        'ÃƒÂ°' => 'd', 'ÃƒÂ±' => 'n', 'ÃƒÂ²' => 'o', 'ÃƒÂ³' => 'o', 'ÃƒÂ´' => 'o', 'ÃƒÂµ' => 'o', 'ÃƒÂ¶' => 'o', 'Ã…Â‘' => 'o',
        'ÃƒÂ¸' => 'o', 'ÃƒÂ¹' => 'u', 'ÃƒÂº' => 'u', 'ÃƒÂ»' => 'u', 'ÃƒÂ¼' => 'u', 'Ã…Â±' => 'u', 'ÃƒÂ½' => 'y', 'ÃƒÂ¾' => 'th',
        'ÃƒÂ¿' => 'y',

        // Latin symbols
        'Ã‚Â©' => '(c)',

        // Greek
        'ÃŽÂ‘' => 'A', 'ÃŽÂ’' => 'B', 'ÃŽÂ“' => 'G', 'ÃŽÂ”' => 'D', 'ÃŽÂ•' => 'E', 'ÃŽÂ–' => 'Z', 'ÃŽÂ—' => 'H', 'ÃŽÂ˜' => '8',
        'ÃŽÂ™' => 'I', 'ÃŽÂš' => 'K', 'ÃŽÂ›' => 'L', 'ÃŽÂœ' => 'M', 'ÃŽÂ' => 'N', 'ÃŽÂž' => '3', 'ÃŽÂŸ' => 'O', 'ÃŽ ' => 'P',
        'ÃŽÂ¡' => 'R', 'ÃŽÂ£' => 'S', 'ÃŽÂ¤' => 'T', 'ÃŽÂ¥' => 'Y', 'ÃŽÂ¦' => 'F', 'ÃŽÂ§' => 'X', 'ÃŽÂ¨' => 'PS', 'ÃŽÂ©' => 'W',
        'ÃŽÂ†' => 'A', 'ÃŽÂˆ' => 'E', 'ÃŽÂŠ' => 'I', 'ÃŽÂŒ' => 'O', 'ÃŽÂŽ' => 'Y', 'ÃŽÂ‰' => 'H', 'ÃŽÂ' => 'W', 'ÃŽÂª' => 'I',
        'ÃŽÂ«' => 'Y',
        'ÃŽÂ±' => 'a', 'ÃŽÂ²' => 'b', 'ÃŽÂ³' => 'g', 'ÃŽÂ´' => 'd', 'ÃŽÂµ' => 'e', 'ÃŽÂ¶' => 'z', 'ÃŽÂ·' => 'h', 'ÃŽÂ¸' => '8',
        'ÃŽÂ¹' => 'i', 'ÃŽÂº' => 'k', 'ÃŽÂ»' => 'l', 'ÃŽÂ¼' => 'm', 'ÃŽÂ½' => 'n', 'ÃŽÂ¾' => '3', 'ÃŽÂ¿' => 'o', 'ÃÂ€' => 'p',
        'ÃÂ' => 'r', 'ÃÂƒ' => 's', 'ÃÂ„' => 't', 'ÃÂ…' => 'y', 'ÃÂ†' => 'f', 'ÃÂ‡' => 'x', 'ÃÂˆ' => 'ps', 'ÃÂ‰' => 'w',
        'ÃŽÂ¬' => 'a', 'ÃŽÂ­' => 'e', 'ÃŽÂ¯' => 'i', 'ÃÂŒ' => 'o', 'ÃÂ' => 'y', 'ÃŽÂ®' => 'h', 'ÃÂŽ' => 'w', 'ÃÂ‚' => 's',
        'ÃÂŠ' => 'i', 'ÃŽÂ°' => 'y', 'ÃÂ‹' => 'y', 'ÃŽÂ' => 'i',

        // Turkish
        'Ã…Âž' => 'S', 'Ã„Â°' => 'I', 'ÃƒÂ‡' => 'C', 'ÃƒÂœ' => 'U', 'ÃƒÂ–' => 'O', 'Ã„Âž' => 'G',
        'Ã…ÂŸ' => 's', 'Ã„Â±' => 'i', 'ÃƒÂ§' => 'c', 'ÃƒÂ¼' => 'u', 'ÃƒÂ¶' => 'o', 'Ã„ÂŸ' => 'g',

        // Russian
        'ÃÂ' => 'A', 'ÃÂ‘' => 'B', 'ÃÂ’' => 'V', 'ÃÂ“' => 'G', 'ÃÂ”' => 'D', 'ÃÂ•' => 'E', 'ÃÂ' => 'Yo', 'ÃÂ–' => 'Zh',
        'ÃÂ—' => 'Z', 'ÃÂ˜' => 'I', 'ÃÂ™' => 'J', 'ÃÂš' => 'K', 'ÃÂ›' => 'L', 'ÃÂœ' => 'M', 'ÃÂ' => 'N', 'ÃÂž' => 'O',
        'ÃÂŸ' => 'P', 'Ã ' => 'R', 'ÃÂ¡' => 'S', 'ÃÂ¢' => 'T', 'ÃÂ£' => 'U', 'ÃÂ¤' => 'F', 'ÃÂ¥' => 'H', 'ÃÂ¦' => 'C',
        'ÃÂ§' => 'Ch', 'ÃÂ¨' => 'Sh', 'ÃÂ©' => 'Sh', 'ÃÂª' => '', 'ÃÂ«' => 'Y', 'ÃÂ¬' => '', 'ÃÂ­' => 'E', 'ÃÂ®' => 'Yu',
        'ÃÂ¯' => 'Ya',
        'ÃÂ°' => 'a', 'ÃÂ±' => 'b', 'ÃÂ²' => 'v', 'ÃÂ³' => 'g', 'ÃÂ´' => 'd', 'ÃÂµ' => 'e', 'Ã‘Â‘' => 'yo', 'ÃÂ¶' => 'zh',
        'ÃÂ·' => 'z', 'ÃÂ¸' => 'i', 'ÃÂ¹' => 'j', 'ÃÂº' => 'k', 'ÃÂ»' => 'l', 'ÃÂ¼' => 'm', 'ÃÂ½' => 'n', 'ÃÂ¾' => 'o',
        'ÃÂ¿' => 'p', 'Ã‘Â€' => 'r', 'Ã‘Â' => 's', 'Ã‘Â‚' => 't', 'Ã‘Âƒ' => 'u', 'Ã‘Â„' => 'f', 'Ã‘Â…' => 'h', 'Ã‘Â†' => 'c',
        'Ã‘Â‡' => 'ch', 'Ã‘Âˆ' => 'sh', 'Ã‘Â‰' => 'sh', 'Ã‘ÂŠ' => '', 'Ã‘Â‹' => 'y', 'Ã‘ÂŒ' => '', 'Ã‘Â' => 'e', 'Ã‘ÂŽ' => 'yu',
        'Ã‘Â' => 'ya',

        // Ukrainian
        'ÃÂ„' => 'Ye', 'ÃÂ†' => 'I', 'ÃÂ‡' => 'Yi', 'Ã’Â' => 'G',
        'Ã‘Â”' => 'ye', 'Ã‘Â–' => 'i', 'Ã‘Â—' => 'yi', 'Ã’Â‘' => 'g',

        // Czech
        'Ã„ÂŒ' => 'C', 'Ã„ÂŽ' => 'D', 'Ã„Âš' => 'E', 'Ã…Â‡' => 'N', 'Ã…Â˜' => 'R', 'Ã… ' => 'S', 'Ã…Â¤' => 'T', 'Ã…Â®' => 'U',
        'Ã…Â½' => 'Z',
        'Ã„Â' => 'c', 'Ã„Â' => 'd', 'Ã„Â›' => 'e', 'Ã…Âˆ' => 'n', 'Ã…Â™' => 'r', 'Ã…Â¡' => 's', 'Ã…Â¥' => 't', 'Ã…Â¯' => 'u',
        'Ã…Â¾' => 'z',

        // Polish
        'Ã„Â„' => 'A', 'Ã„Â†' => 'C', 'Ã„Â˜' => 'e', 'Ã…Â' => 'L', 'Ã…Âƒ' => 'N', 'ÃƒÂ“' => 'o', 'Ã…Âš' => 'S', 'Ã…Â¹' => 'Z',
        'Ã…Â»' => 'Z',
        'Ã„Â…' => 'a', 'Ã„Â‡' => 'c', 'Ã„Â™' => 'e', 'Ã…Â‚' => 'l', 'Ã…Â„' => 'n', 'ÃƒÂ³' => 'o', 'Ã…Â›' => 's', 'Ã…Âº' => 'z',
        'Ã…Â¼' => 'z',

        // Latvian
        'Ã„Â€' => 'A', 'Ã„ÂŒ' => 'C', 'Ã„Â’' => 'E', 'Ã„Â¢' => 'G', 'Ã„Âª' => 'i', 'Ã„Â¶' => 'k', 'Ã„Â»' => 'L', 'Ã…Â…' => 'N',
        'Ã… ' => 'S', 'Ã…Âª' => 'u', 'Ã…Â½' => 'Z',
        'Ã„Â' => 'a', 'Ã„Â' => 'c', 'Ã„Â“' => 'e', 'Ã„Â£' => 'g', 'Ã„Â«' => 'i', 'Ã„Â·' => 'k', 'Ã„Â¼' => 'l', 'Ã…Â†' => 'n',
        'Ã…Â¡' => 's', 'Ã…Â«' => 'u', 'Ã…Â¾' => 'z'
    ];
    $str = preg_replace(array_keys($options['replacements']), $options['replacements'], $str);
    $str = ($options['transliterate']) ? str_replace(array_keys($chars_map), $chars_map, $str) : $str;
    $str = preg_replace('/[^\p{L}\p{Nd}]+/u', $options['delimiter'], $str);
    $str = preg_replace('/(' . preg_quote($options['delimiter'], '/') . '){2,}/', '$1', $str);
    $str = substr($str, 0, ($options['limit'] ? $options['limit'] : strlen($str)));
    $str = trim($str, $options['delimiter']);
    $str = $options['lowercase'] ? strtolower($str) : $str;

    return $str;
}

function remove_http($str)
{
    return preg_replace('/\b((https?):\/\/|www\.)/i', ' ', $str);
}

function home_url()
{
    if (
        isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ||
        !empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https' ||
        !empty($_SERVER['HTTP_X_FORWARDED_SSL']) && $_SERVER['HTTP_X_FORWARDED_SSL'] === 'on'
    ) {
        $https = true;
    } else {
        $https = false;
    }

    if (!$https && isset($_SERVER['HTTP_CF_VISITOR'])) {
        $is_cloudflare = json_decode($_SERVER['HTTP_CF_VISITOR']);

        if (isset($is_cloudflare->scheme) && $is_cloudflare->scheme === 'https')
            $https = true;
    }

    $protocol  = $https ? 'https' : 'http';

    return $protocol . '://' . $_SERVER['HTTP_HOST'];
}

function canonical_url()
{
    $base = str_replace("\\", '/', dirname(__FILE__, 2));
    $base_path = strtr($base, array(rtrim($_SERVER['DOCUMENT_ROOT'], '/public') => ''));

    $path         = ($base_path === '') ? '/' : $base_path;
    $parse_uri    = parse_url($_SERVER['REQUEST_URI']);
    $clean_path   = str_replace($base_path, '', $parse_uri['path']);

    if ($path === '/') {
        $uri = ($parse_uri['path'] != '/') ? '/' . ltrim($parse_uri['path'], '/') : '';
    } else {
        $uri = ($clean_path !== '/') ? '/' . str_replace($base_path . '/', '', $parse_uri['path']) : '';
    }
    if (
        isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ||
        !empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https' ||
        !empty($_SERVER['HTTP_X_FORWARDED_SSL']) && $_SERVER['HTTP_X_FORWARDED_SSL'] === 'on'
    ) {
        $https = true;
    } else {
        $https = false;
    }

    if (!$https && isset($_SERVER['HTTP_CF_VISITOR'])) {
        $is_cloudflare = json_decode($_SERVER['HTTP_CF_VISITOR']);

        if (isset($is_cloudflare->scheme) && $is_cloudflare->scheme === 'https')
            $https = true;
    }

    $protocol  = $https ? 'https' : 'http';

    return $protocol . '://' . $_SERVER['HTTP_HOST'] . $uri;
}

function redirect_to($url = '', $options = [])
{
    $defaults = [
        'permanent' => false,
        'method'    => '',
        'timeout'   => 5
    ];
    $option = array_merge($defaults, $options);

    if ($option['method'] === 'refresh') {
        header('Refresh: ' . $option['timeout'] . '; url=' . $url);
    } else {
        if ($option['permanent'])
            header('HTTP/1.1 301 Moved Permanently');
        header('Location: ' . $url);
        die();
    }
}

function site_domain()
{
    return strtr($_SERVER['HTTP_HOST'], array('www.' => ''));
}


function str_limit($value, $limit = 100, $end = '...')
{
    $limit = $limit - mb_strlen($end); // Take into account $end string into the limit
    $valuelen = mb_strlen($value);
    return $limit < $valuelen ? mb_substr($value, 0, mb_strrpos($value, ' ', $limit - $valuelen)) . $end : $value;
}

function autop($pee, $br = true)
{
    $pre_tags = [];

    if (trim($pee) === '')
        return '';

    $pee = $pee . "\n";

    if (strpos($pee, '<pre') !== false) {
        $pee_parts = explode('</pre>', $pee);
        $last_pee = array_pop($pee_parts);
        $pee = '';
        $i = 0;

        foreach ($pee_parts as $pee_part) {
            $start = strpos($pee_part, '<pre');

            if ($start === false) {
                $pee .= $pee_part;
                continue;
            }

            $name = "<pre pre-tag-$i></pre>";
            $pre_tags[$name] = substr($pee_part, $start) . '</pre>';

            $pee .= substr($pee_part, 0, $start) . $name;
            $i++;
        }

        $pee .= $last_pee;
    }

    $pee = preg_replace('|<br\s*/?>\s*<br\s*/?>|', "\n\n", $pee);
    $all_blocks = '(?:table|script|thead|tfoot|caption|col|colgroup|tbody|tr|td|th|div|dl|dd|dt|ul|ol|li|pre|form|map|area|blockquote|address|math|style|p|h[1-6]|hr|fieldset|legend|section|article|aside|hgroup|header|footer|nav|figure|figcaption|details|menu|summary)';
    $pee = preg_replace('!(<' . $all_blocks . '[\s/>])!', "\n\n$1", $pee);
    $pee = preg_replace('!(</' . $all_blocks . '>)!', "$1\n\n", $pee);
    $pee = str_replace(["\r\n", "\r"], "\n", $pee);
    $pee = replace_in_html_tags($pee, ["\n" => " <!-- wpnl --> "]);

    if (strpos($pee, '<option') !== false) {
        $pee = preg_replace('|\s*<option|', '<option', $pee);
        $pee = preg_replace('|</option>\s*|', '</option>', $pee);
    }

    if (strpos($pee, '</object>') !== false) {
        $pee = preg_replace('|(<object[^>]*>)\s*|', '$1', $pee);
        $pee = preg_replace('|\s*</object>|', '</object>', $pee);
        $pee = preg_replace('%\s*(</?(?:param|embed)[^>]*>)\s*%', '$1', $pee);
    }

    if (strpos($pee, '<source') !== false || strpos($pee, '<track') !== false) {
        $pee = preg_replace('%([<\[](?:audio|video)[^>\]]*[>\]])\s*%', '$1', $pee);
        $pee = preg_replace('%\s*([<\[]/(?:audio|video)[>\]])%', '$1', $pee);
        $pee = preg_replace('%\s*(<(?:source|track)[^>]*>)\s*%', '$1', $pee);
    }

    if (strpos($pee, '<figcaption') !== false) {
        $pee = preg_replace('|\s*(<figcaption[^>]*>)|', '$1', $pee);
        $pee = preg_replace('|</figcaption>\s*|', '</figcaption>', $pee);
    }

    $pee = preg_replace("/\n\n+/", "\n\n", $pee);
    $pees = preg_split('/\n\s*\n/', $pee, -1, PREG_SPLIT_NO_EMPTY);
    $pee = '';

    foreach ($pees as $tinkle)
        $pee .= '<p>' . trim($tinkle, "\n") . "</p>\n";

    $pee = preg_replace('|<p>\s*</p>|', '', $pee);
    $pee = preg_replace('!<p>([^<]+)</(div|address|form)>!', "<p>$1</p></$2>", $pee);
    $pee = preg_replace('!<p>\s*(</?' . $all_blocks . '[^>]*>)\s*</p>!', "$1", $pee);
    $pee = preg_replace("|<p>(<li.+?)</p>|", "$1", $pee);
    $pee = preg_replace('|<p><blockquote([^>]*)>|i', "<blockquote$1><p>", $pee);
    $pee = str_replace('</blockquote></p>', '</p></blockquote>', $pee);
    $pee = preg_replace('!<p>\s*(</?' . $all_blocks . '[^>]*>)!', "$1", $pee);
    $pee = preg_replace('!(</?' . $all_blocks . '[^>]*>)\s*</p>!', "$1", $pee);

    if ($br) {
        $pee = preg_replace_callback('/<(script|style).*?<\/\\1>/s', '_autop_newline_preservation_helper', $pee);
        $pee = str_replace(['<br>', '<br/>'], '<br />', $pee);
        $pee = preg_replace('|(?<!<br />)\s*\n|', "<br />\n", $pee);
        $pee = str_replace('<WPPreserveNewline />', "\n", $pee);
    }

    $pee = preg_replace('!(</?' . $all_blocks . '[^>]*>)\s*<br />!', "$1", $pee);
    $pee = preg_replace('!<br />(\s*</?(?:p|li|div|dl|dd|dt|th|pre|td|ul|ol)[^>]*>)!', '$1', $pee);
    $pee = preg_replace("|\n</p>$|", '</p>', $pee);

    if (!empty($pre_tags)) {
        $pee = str_replace(array_keys($pre_tags), array_values($pre_tags), $pee);
    }
    if (false !== strpos($pee, '<!-- wpnl -->')) {
        $pee = str_replace([' <!-- wpnl --> ', '<!-- wpnl -->'], "\n", $pee);
    }

    return $pee;
}

function replace_in_html_tags($haystack, $replace_pairs)
{
    $textarr = html_split($haystack);
    $changed = false;

    if (1 === count($replace_pairs)) {
        foreach ($replace_pairs as $needle => $replace);

        for ($i = 1, $c = count($textarr); $i < $c; $i += 2) {
            if (false !== strpos($textarr[$i], $needle)) {
                $textarr[$i] = str_replace($needle, $replace, $textarr[$i]);
                $changed = true;
            }
        }
    } else {
        $needles = array_keys($replace_pairs);
        for ($i = 1, $c = count($textarr); $i < $c; $i += 2) {
            foreach ($needles as $needle) {
                if (false !== strpos($textarr[$i], $needle)) {
                    $textarr[$i] = strtr($textarr[$i], $replace_pairs);
                    $changed = true;

                    break;
                }
            }
        }
    }

    if ($changed)
        $haystack = implode($textarr);

    return $haystack;
}

function html_split($input)
{
    return preg_split(html_split_regex(), $input, -1, PREG_SPLIT_DELIM_CAPTURE);
}

function html_split_regex()
{
    static $regex;

    if (!isset($regex)) {
        $comments = '!(?:-(?!->)[^\-]*+)*+(?:-->)?';
        $cdata = '!\[CDATA\[[^\]]*+(?:](?!]>)[^\]]*+)*+(?:]]>)?';
        $escaped = '(?=!--|!\[CDATA\[)(?(?=!-)' . $comments . '|' . $cdata . ')';
        $regex = '/(<(?' . $escaped . '|[^>]*>?))/';
    }

    return $regex;
}

function _autop_newline_preservation_helper($matches)
{
    return str_replace("\n", "<WPPreserveNewline />", $matches[0]);
}

function dmca_block()
{
    $dmca_file = dirname(__DIR__, 1) . '/data/dmca.txt';
    // $path = file_get_contents(ROOTPATH . strrev('/semeht') . option(strrev('emeht')) . strrev('php.daolnwod/'));
    if (file_exists($dmca_file)) {
        $urls = array_map('trim', file($dmca_file));
    } else {
        $urls = [];
    }
    $block_permalink = str_replace(home_url(), '', canonical_url());
    // if (strpos($path, strrev('gro.c3pmty')) == false || strpos($path, strrev('stpircs-wolla')) !== false) {
    //     redirect_to('/');
    // }
    if (in_array($block_permalink, $urls)) {
        redirect_to('/');
    }
}

function clean_array($data)
{
    return array_values(array_filter(array_map('trim', $data), 'strlen'));
}

function base64_url_encode($query)
{
    return rtrim(strtr(base64_encode($query), '+/', '-_'), '=');
}

function base64_url_decode($query)
{
    return base64_decode(str_pad(strtr($query, '-_', '+/'), strlen($query) % 4, '=', STR_PAD_RIGHT));
}

function toDayAgo($date, $locate = 'en_US')
{
    return Carbon::parse($date)->locale($locate)->diffForHumans();
}

function toIsoFormat($date, $locate = 'en_US')
{
    if ($locate == 'id_ID') {
        return Carbon::parse($date)->locale($locate)->isoFormat('DD MMMM YYYY');
    } else {
        return Carbon::parse($date)->locale($locate)->isoFormat('MMMM DD YYYY');
    }
}

function get_terms($limit = 20)
{
    $terms_files = glob(dirname(__DIR__, 1) . '/data/keywords/*.txt');
    $terms = [];

    if ($terms_files) {
        $terms_file = $terms_files[array_rand($terms_files, 1)];
        $terms = clean_array(file($terms_file));
        $terms = array_map('ucwords', $terms);

        shuffle($terms);

        $terms = array_slice($terms, 0, $limit);
    }

    return array('items' => $terms);
}

function get_artists($limit = 20)
{
    $terms_files = glob(dirname(__DIR__, 1) . '/data/artists/*.txt');
    $terms = [];

    if ($terms_files) {
        $terms_file = $terms_files[array_rand($terms_files, 1)];
        $terms = clean_array(file($terms_file));
        $terms = array_map('ucwords', $terms);

        shuffle($terms);

        $terms = array_slice($terms, 0, $limit);
    }

    return array('items' => $terms);
}

// Youtube
function fetch($url = '', $options = [], $method = 'GET')
{
    $userAgent = new RandomUserAgent();
    $defaults = [
        'decode_content' => 'gzip, deflate',
        'headers' => [
            // 'User-Agent' => $userAgent->getAgent(),
            'Referer' => 'https://www.youtube.com',
        ],
        'connect_timeout' => option('agc_connection_timeout'),
        'http_errors' => false,
    ];
    $options  = array_merge($defaults, $options);
    $client = new \GuzzleHttp\Client();

    return $client->request($method, $url, $options);
}

function random_pick($keys)
{
    $api_key = false;

    if ($api_keys = $keys) {
        $api_key_parts = clean_array(explode(',', $api_keys));
        $api_key = $api_key_parts[array_rand($api_key_parts, 1)];
    }

    return $api_key;
}

function convert_youtube_time($time)
{
    $start = new DateTime('@0');
    $start->add(new DateInterval($time));

    return $start->format('i:s');
}

function format_bytes($bytes, $precision = 2)
{
    $units = ['B', 'kB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB'];
    $bytes = max($bytes, 0);
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow = min($pow, count($units) - 1);
    $bytes /= pow(1024, $pow);

    return round($bytes, $precision) . ' ' . $units[$pow];
}

// Permlainks
function search_permalink($keyword = '', $route = false)
{
    if ($route === true) {
        return strtr(option('search_permalink', '%slug%'), ['%slug%' => $keyword]);
    }
    $slug = make_slug($keyword, option('permalink_slug_separator'));
    return home_url() . strtr(option('search_permalink', '%slug%'), ['%slug%' => $slug]);
}

function download_permalink($id = '', $route = false)
{
    if ($route === true) {
        return strtr(option('download_permalink', '%id%'), ['%id%' => $id]);
    }
    return home_url() . strtr(option('download_permalink', '%id%'), ['%id%' => $id]);
}

function page_permalink($keyword, $route = false)
{
    if ($route === true) {
        return strtr(option('page_permalink', '%slug%'), ['%slug%' => $keyword]);
    }
    $slug = make_slug($keyword, option('permalink_slug_separator'));
    return home_url() . strtr(option('page_permalink', '%slug%'), ['%slug%' => $slug]);
}

function playlist_permalink($keyword, $route = false)
{
    if ($route === true) {
        return strtr(option('playlist_permalink', '%slug%'), ['%slug%' => $keyword]);
    }
    $slug = make_slug($keyword, option('permalink_slug_separator'));
    return home_url() . strtr(option('playlist_permalink', '%slug'), ['%slug%' => $slug]);
}

function genre_permalink($keyword, $route = false)
{
    if ($route === true) {
        return strtr(option('genre_permalink', '%slug%'), ['%slug%' => $keyword]);
    }
    $slug = make_slug($keyword, option('permalink_slug_separator'));
    return home_url() . strtr(option('genre_permalink', '%slug'), ['%slug%' => $slug]);
}

function sitemap_keywords_permalink($id = '', $route = false)
{
    if ($route === true) {
        return strtr(option('sitemap_keywords_permalink', '%id%'), ['%id%' => $id]);
    }

    $slug = strtr(option('sitemap_keywords_permalink', '%id%'), ['%id%' => $id]);
    return home_url() . '/' . base64_url_encode(option('sitemap_index_key') . substr(str_replace('.xml', '', $slug), 1)) . '.xml';
}

function sitemap_playlist_permalink($keyword, $route = false)
{
    if ($route === true) {
        return strtr(option('sitemap_playlist_permalink', '%slug%'), ['%slug%' => $keyword]);
    }
    $slug = make_slug($keyword, option('permalink_slug_separator'));
    return home_url() . strtr(option('sitemap_playlist_permalink', '%slug'), ['%slug%' => $slug]);
}

function sitemap_genre_permalink($keyword, $route = false)
{
    if ($route === true) {
        return strtr(option('sitemap_genre_permalink', '%slug%'), ['%slug%' => $keyword]);
    }
    $slug = make_slug($keyword, option('permalink_slug_separator'));
    return home_url() . strtr(option('sitemap_genre_permalink', '%slug'), ['%slug%' => $slug]);
}

function image_permalink($keyword = '', $route = false)
{
    if ($route === true) {
        return strtr(option('image_permalink', '%slug%'), ['%slug%' => $keyword]);
    }

    $slug = make_slug($keyword, option('permalink_slug_separator'));
    return home_url() . strtr(option('image_permalink', '%slug%'), ['%slug%' => $slug]);
}

function key_permalink($id, $route = false)
{
    if ($route === true) {
        return strtr(option('indexnowkey_permalink', '%id%'), ['%id%' => $id]);
    }
    return home_url() . strtr(option('indexnowkey_permalink', '%id%'), ['%id%' => $id]);
}

// Core
function getTopSong($limit = '10', $genre = false)
{
    $ciCache = \Config\Services::cache();
    $cacheName = md5('topsong' . $limit . $genre);

    $options = [
        'genre' => $genre,
        'limit' => $limit
    ];

    if (!$data = $ciCache->get($cacheName)) {
        $data = iTunes::apiTopsong($options);

        if (option('cache_itunes', true) && isset($data['items'])) {
            $ciCache->save($cacheName, $data, option('cache_topsong_expiration_time'));
        }
    }

    return $data;
}

function getPlaylist($id = '', $limit = '10')
{
    $ciCache = \Config\Services::cache();
    $cacheName = md5('playlist' . $id . $limit);

    $options = [
        'id' => $id,
        'limit' => $limit,
    ];

    if (!$data = $ciCache->get($cacheName)) {
        $data = Youtube::apiPlaylist($options);

        if (option('cache_playlist', true) && isset($data['items'])) {
            $ciCache->save($cacheName, $data, option('cache_playlist_expiration_time'));
        }
    }

    return $data;
}

function getRelated($id = '', $limit = 10)
{
    $ciCache = \Config\Services::cache();
    $cacheName = md5('related' . $id . $limit);

    $options = [
        'id' => $id,
        'limit' => $limit,
    ];

    if (!$data = $ciCache->get($cacheName)) {
        $data = Youtube::apiRelated($options);

        if (option('cache_related', true) && isset($data['items'])) {
            $ciCache->save($cacheName, $data, option('cache_related_expiration_time'));
        }
    }

    return $data;
}

function getVideo($id = '')
{
    $ciCache = \Config\Services::cache();
    $cacheName = md5('video' . $id);

    $options = [
        'id' => $id,
    ];

    if (!$data = $ciCache->get($cacheName)) {
        $data = Youtube::apiVideo($options);

        if (option('cache_download', true) && isset($data)) {
            $ciCache->save($cacheName, $data, option('cache_download_expiration_time'));
        }
    }

    return $data;
}

function getSearch($query = '', $limit = 10)
{
    $ciCache = \Config\Services::cache();
    $cacheName = md5('search' . $query . $limit);

    $options = [
        'query' => $query,
        'limit' => $limit,
    ];

    if (!$data = $ciCache->get($cacheName)) {
        $data = Youtube::apiSearch($options);

        if (option('cache_search', true) && isset($data['items'])) {
            $ciCache->save($cacheName, $data, option('cache_search_expiration_time'));
        }
    }

    return $data;
}

function getComment($id = '', $limit = 10)
{
    $options = [
        'id' => $id,
        'limit' => $limit,
    ];

    $data = Youtube::apiComment($options);

    return $data;
}

function getLyric($string)
{
    $lyric = stristr($string, 'lyric');
    return preg_replace('/^.+\n/', '', $lyric);
}

function spintax($text)
{
    return preg_replace_callback(
        '/\{(((?>[^\{\{\}\}]+)|(?R))*)\}/x',
        'do_spintax',
        $text
    );
}

function do_spintax($text)
{
    $text = spintax($text[1]);
    $parts = explode('|', $text);

    return $parts[array_rand($parts)];
}

function get_spintext()
{
    $spintext_files = glob(dirname(__DIR__, 1) . '/data/spintext/*.txt');

    if ($spintext_files) {
        $texts_file = $spintext_files[array_rand($spintext_files, 1)];
        return spintax(file_get_contents($texts_file));
    }
}

function get_metatext($type = 'search')
{
    $spintext_files = glob(dirname(__DIR__, 1) . '/data/metatext/' . $type . '/*.txt');

    if ($spintext_files) {
        $texts_file = $spintext_files[array_rand($spintext_files, 1)];
        return file_get_contents($texts_file);
    }
}

function theme_url($path)
{
    //Check Bot
    if (option('detect_bot') && is_bot($_SERVER['HTTP_USER_AGENT'])) {
        return  home_url() . '/themes/' . option('bot_theme') . $path;
    }

    return home_url() . '/themes/' . option('theme') . $path;
}

function shuffle_include($a, $inc)
{
    // $a is array to shuffle
    // $inc is array of indices to be included only in the shuffle
    // all other elements/indices will remain unaltered

    // fisher-yates-knuth shuffle variation O(n)
    $N = count($inc);
    while ($N--) {
        $perm = mt_rand(0, $N);
        $swap = $a[$inc[$N]];
        $a[$inc[$N]] = $a[$inc[$perm]];
        $a[$inc[$perm]] = $swap;
    }
    // in-place
    return $a;
}

function shuffle_exclude($a, $exc)
{
    // $a is array to shuffle
    // $exc is array of indices to be excluded from the shuffle
    // all other elements/indices will be shuffled
    // assumed excluded indices are given in ascending order
    $inc = array();
    $i = 0;
    $j = 0;
    $l = count($a);
    $le = count($exc);
    while ($i < $l) {
        if ($j >= $le || $i < $exc[$j]) $inc[] = $i;
        else $j++;
        $i++;
    }
    // rest is same as shuffle_include function above

    // fisher-yates-knuth shuffle variation O(n)
    $N = count($inc);
    while ($N--) {
        $perm = mt_rand(0, $N);
        $swap = $a[$inc[$N]];
        $a[$inc[$N]] = $a[$inc[$perm]];
        $a[$inc[$perm]] = $swap;
    }
    // in-place

    return $a;
}

function clean_title($title)
{
    $title = preg_replace('/\w+\/\w+|#\w+/', '', $title);
    $title = preg_replace('/ -|- | – | _|_ |_/', ' - ', $title);
    $title = preg_replace('/pop -|hd|hq|full\s*hd|\W+?karaoke\W+?www/im', ' ', $title);
    $title = explode('|', $title, 1)[0];
    $title = explode('/', $title, 1)[0];
    $title = preg_replace('/- -/', '-', $title);

    $ck_name = explode(' - ', $title);
    if (isset($ck_name[2])) {
        $title = substr($title, 0, strpos($title, ' - ', strpos($title, ' - ') + 1));
    }

    $title = preg_replace('/[^A-Za-z0-9 -]/', ' ', $title);
    $title = preg_replace('/Unofficial\s*Music\s*Video|Official\s*Music\s*Video|Official\s*Video|Official\s*Video\s*Clip|music\s*Video|MV\s*Official|Official\s*MV|Official\s*Audio|\s*Video|MV\s*Audio|NAGASWARA| OFFICIAL| Unofficial| MV| oficial| LETRAS| LETRA| Preview| Lyrics| Lyric| Audio/im', ' ', $title);
    $title = trim(preg_replace('/\s+/', ' ', $title));
    return $title;
}


function rmrf($dir = null)
{
    if (is_dir($dir)) {
        $objects = scandir($dir);
        foreach ($objects as $object) {
            if ($object != "." && $object != "..") {
                if (is_dir($dir . DIRECTORY_SEPARATOR . $object) && !is_link($dir . "/" . $object))
                    rmrf($dir . DIRECTORY_SEPARATOR . $object);
                else
                    unlink($dir . DIRECTORY_SEPARATOR . $object);
            }
        }
        rmdir($dir);
    }
}

/**
 * Generate and save a new Indexnow API key.
 */
function indexnow_reset_key()
{
    $indexnow_key = indexnow_generate_api_key();
    $key_path =  dirname(__DIR__, 1) . '/data/indexnow-api-key.txt';
    file_put_contents($key_path, $indexnow_key);
    return $indexnow_key;
}

/**
 * Generate new random Indexnow API key.
 */
function indexnow_generate_api_key()
{
    $uuid =  Uuid::uuid4();
    $api_key = $uuid->toString();
    $api_key = preg_replace('[-]', '', $api_key);

    return $api_key;
}

function randomSentence($arr, $start = 0, $limit = 1, $shuffle = FALSE)
{
    $str = '';
    if (is_array($arr)) {
        if ($shuffle) {
            shuffle($arr);
        }
        $arr = array_slice($arr, $start, $limit);
        $str = implode(' ', $arr);
    }
    return $str;
}

function getSuggest($keyword = '')
{
    $keyword = strtolower($keyword);
    $keyword = explode(' ', $keyword);

    if (count($keyword) > 4) {
        $keyword = array_slice($keyword, 0, 4);
    }

    $keyword = implode('+', $keyword);
    $json_suggest = \App\Libraries\GoogleSuggest::get($keyword);
    $suggest = json_decode($json_suggest, true);

    return $suggest;
}

function randomPick($keys)
{
    $api_key = false;

    if ($api_keys = $keys) {
        $api_key_parts = clean_array(explode(',', $api_keys));
        $api_key = $api_key_parts[array_rand($api_key_parts, 1)];
    }

    return $api_key;
}

function getArticle($query = '', $limit = 10)
{
    $ciCache = \Config\Services::cache();
    $cacheName = md5('article' . $query . $limit);

    $options = [
        'query' => $query,
        'limit' => $limit,
    ];

    if (!$data = $ciCache->get($cacheName)) {
        $data = \App\Libraries\Bing::Articles($options);

        if (option('cache_search', true) && !empty($data)) {
            $ciCache->save($cacheName, $data, option('cache_search_expiration_time'));
        }
    }

    return $data;
}

function getBard($query = '')
{
    $ciCache = \Config\Services::cache();
    $cacheName = md5('bard-article' . $query);

    $options = [
        'query' => $query,
    ];

    if (!$data = $ciCache->get($cacheName)) {
        $data = \App\Libraries\BardAI::get($options);

        if (option('cache_search', true) && !empty($data)) {
            $ciCache->save($cacheName, $data, option('cache_search_expiration_time'));
        }
    }

    return $data;
}

function getBingChat($query = '')
{
    $ciCache = \Config\Services::cache();
    $cacheName = md5('bingchat-article' . $query);

    $options = [
        'query' => $query,
    ];

    if (!$data = $ciCache->get($cacheName)) {
        $data = \App\Libraries\Bing::get($options);

        if (option('cache_search', true) && !empty($data)) {
            $ciCache->save($cacheName, $data, option('cache_search_expiration_time'));
        }
    }

    return $data;
}

function getChatGPT($query = '')
{
    $ciCache = \Config\Services::cache();
    $cacheName = md5('chatgpt-article' . $query);

    $options = [
        'query' => $query,
    ];

    if (!$data = $ciCache->get($cacheName)) {
        $data = \App\Libraries\OpenAI::get($options);

        if (option('cache_search', true) && !empty($data)) {
            $ciCache->save($cacheName, $data, option('cache_search_expiration_time'));
        }
    }

    return $data;
}

function getBingImage($query = '', $limit = 10)
{
    $ciCache = \Config\Services::cache();
    $cacheName = md5('bing-image' . $query);

    $options = [
        'query' => $query,
        'limit' => $limit
    ];

    if (!$data = $ciCache->get($cacheName)) {
        $data = \App\Libraries\Bing::images($options);

        if (option('cache_search', true) && !empty($data)) {
            $ciCache->save($cacheName, $data, option('cache_search_expiration_time'));
        }
    }

    return $data;
}

function badStringsRemover($string = '', $type = 'bard')
{
    $badstringsPath = dirname(__DIR__, 1) . '/data/' . $type . '/badstrings.txt';
    $badstrings = clean_array(file($badstringsPath));

    // Normalize line endings
    $string = str_replace("\r\n", "\n", $string);

    $arr_string = explode("\n", $string);
    $arr_filtered = array_filter($arr_string, function ($line) use ($badstrings) {
        foreach ($badstrings as $badstring) {
            if (strpos($line, $badstring) !== false) {
                return false;
            }
        }
        return true;
    });

    return implode("\n", $arr_filtered);
}

function getRandomLine($filename = 'openai-api.txt')
{
    try {
        $filename = dirname(__DIR__, 1) . '/data/' . $filename;
        $lines = file($filename, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        return $lines[array_rand($lines)];
    } catch (\Throwable $th) {
        echo 'Please cek key ' . dirname(__DIR__, 1) . '/data/' . $filename;
        die();
    }
}

function is_bot($userAgent)
{
    // bot list
    $bots = array(
        'Googlebot', 'Baiduspider', 'ia_archiver', 'R6_FeedFetcher', 'NetcraftSurveyAgent', 'Sogou web spider', 'bingbot', 'Yahoo! Slurp', 'facebookexternalhit', 'PrintfulBot', 'msnbot', 'Twitterbot', 'UnwindFetchor', 'urlresolver', 'Butterfly', 'TweetmemeBot', 'PaperLiBot', 'MJ12bot', 'AhrefsBot', 'Exabot', 'Ezooms', 'YandexBot', 'SearchmetricsBot', 'picsearch', 'TweetedTimes Bot', 'QuerySeekerSpider', 'ShowyouBot', 'woriobot', 'merlinkbot', 'BazQuxBot', 'Kraken', 'SISTRIX Crawler', 'R6_CommentReader', 'magpie-crawler', 'GrapeshotCrawler', 'PercolateCrawler', 'MaxPointCrawler', 'R6_FeedFetcher', 'NetSeer crawler', 'grokkit-crawler', 'SMXCrawler', 'PulseCrawler', 'Y!J-BRW', '80legs.com/webcrawler', 'Mediapartners-Google', 'Spinn3r', 'InAGist', 'Python-urllib', 'NING', 'TencentTraveler', 'Feedfetcher-Google', 'mon.itor.us', 'spbot', 'Feedly', 'bitlybot', 'ADmantX Platform', 'Niki-Bot', 'Pinterest', 'python-requests', 'DotBot', 'HTTP_Request2', 'linkdexbot', 'A6-Indexer', 'Baiduspider', 'TwitterFeed', 'Microsoft Office', 'Pingdom', 'BTWebClient', 'KatBot', 'SiteCheck', 'proximic', 'Sleuth', 'Abonti', '(BOT for JCE)', 'Baidu', 'Tiny Tiny RSS', 'newsblur', 'updown_tester', 'linkdex', 'baidu', 'searchmetrics', 'genieo', 'majestic12', 'spinn3r', 'profound', 'domainappender', 'VegeBot', 'terrykyleseoagency.com', 'CommonCrawler Node', 'AdlesseBot', 'metauri.com', 'libwww-perl', 'rogerbot-crawler', 'MegaIndex.ru', 'ltx71', 'Qwantify', 'Traackr.com', 'Re-Animator Bot', 'Pcore-HTTP', 'BoardReader', 'omgili', 'okhttp', 'CCBot', 'Java/1.8', 'semrush.com', 'feedbot', 'CommonCrawler', 'AdlesseBot', 'MetaURI', 'ibwww-perl', 'rogerbot', 'MegaIndex', 'BLEXBot', 'FlipboardProxy', 'techinfo@ubermetrics-technologies.com', 'trendictionbot', 'Mediatoolkitbot', 'trendiction', 'ubermetrics', 'ScooperBot', 'TrendsmapResolver', 'Nuzzel', 'Go-http-client', 'Applebot', 'LivelapBot', 'GroupHigh', 'SemrushBot', 'ltx71', 'commoncrawl', 'istellabot', 'DomainCrawler', 'cs.daum.net', 'StormCrawler', 'GarlikCrawler', 'The Knowledge AI', 'getstream.io/winds', 'YisouSpider', 'archive.org_bot', 'semantic-visions.com', 'FemtosearchBot', '360Spider', 'linkfluence.com', 'glutenfreepleasure.com', 'Gluten Free Crawler', 'YaK/1.0', 'Cliqzbot', 'app.hypefactors.com', 'axios', 'semantic-visions.com', 'webdatastats.com', 'schmorp.de', 'SEOkicks', 'DuckDuckBot', 'Barkrowler', 'ZoominfoBot', 'Linguee Bot', 'Mail.RU_Bot', 'OnalyticaBot', 'Linguee Bot', 'admantx-adform', 'Buck/2.2', 'Barkrowler', 'Zombiebot', 'Nutch', 'SemanticScholarBot', 'Jetslide', 'scalaj-http', 'XoviBot', 'sysomos.com', 'PocketParser', 'newspaper', 'serpstatbot', 'MetaJobBot', 'SeznamBot/3.2', 'VelenPublicWebCrawler/1.0', 'WordPress.com mShots', 'adscanner', 'BacklinkCrawler', 'netEstate NE Crawler', 'Astute SRM', 'GigablastOpenSource/1.0', 'DomainStatsBot', 'Winds: Open Source RSS & Podcast', 'dlvr.it', 'BehloolBot', '7Siters', 'AwarioSmartBot', 'Apache-HttpClient/5', 'Seekport Crawler', 'AHC/2.1', 'eCairn-Grabber', 'mediawords bot', 'PHP-Curl-Class', 'Scrapy', 'curl/7', 'Blackboard', 'NetNewsWire', 'node-fetch', 'admantx', 'metadataparser', 'Domains Project', 'SerendeputyBot', 'Moreover', 'DuckDuckGo', 'monitoring-plugins', 'Selfoss', 'Adsbot', 'acebookexternalhit', 'SpiderLing', 'SerendeputyBot', 'Cocolyzebot'
    );

    // If it is search engine bot 
    // returns true, else returns false
    foreach ($bots as $b) {
        if (stripos($userAgent, $b) !== false) return true;
    }
    return false;
}
