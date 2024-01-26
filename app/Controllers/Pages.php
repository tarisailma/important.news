<?php

namespace App\Controllers;

use Google;
use Google_Service_Indexing;
use Google_Service_Indexing_UrlNotification;

class Pages extends BaseController
{
  public function Index()
  {
    if (isset($_GET['q'])) {
      $slug = search_permalink($_GET['q']);
      return redirect_to($slug);
    }

    $meta = [
      'title' => strtr(option('home_title', '%site_tagline%'), [
        '%site_tagline%' => option('site_tagline'),
        '%site_name%' => option('site_name')
      ]),
      'description' => strtr(option('home_meta_description'), [
        '%site_name%' => option('site_name'),
        '%site_tagline%' => option('site_tagline'),
        '%domain%' => site_domain()
      ]),
      'robots' => option('home_meta_robots')
    ];

    $data = [
      'title' => $meta['title'],
      'description' => $meta['description'],
      'robots' => $meta['robots'],
    ];

    //Check Bot
    if (option('detect_bot') && is_bot($_SERVER['HTTP_USER_AGENT'])) {
      return view(option('bot_theme') . '/index', $data);
    }

    return view(option('theme') . '/index', $data);
  }

  public function Search($slug)
  {
    $ciCache = \Config\Services::cache();
    $cacheName = md5($slug . 'description');
    $article = [];
    dmca_block();

    if (option('indexing_auto') == 'true') {
      $indexnow = $this->AutoIndexNow();
      $indexing = $this->AutoIndexing();
    } else {
      $indexnow = 'false';
      $indexing = 'false';
    }

    $query = ucwords(str_replace(option('permalink_slug_separator'), ' ', $slug));
    $search = getSearch($query, option('youtube_search_limit'));

    if (isset($search['items'])) {

      if (!$meta = $ciCache->get($cacheName)) {
        $meta = [
          'title' => spintax(strtr(option('search_title', '%site_tagline%'), [
            '%query%' => $query,
            '%title%' => $search['items'][0]['title'],
            '%titleClean%' => $search['items'][0]['titleClean'],
            '%size%' => $search['items'][0]['size'],
            '%duration%' => $search['items'][0]['duration'],
            '%slug%' => $slug,
            '%site_name%' => option('site_name')
          ])),
          'description' => spintax(strtr(option('search_meta_description') ? option('search_meta_description') : get_metatext(), [
            '%query%' => $query,
            '%title%' => $search['items'][0]['title'],
            '%titleClean%' => $search['items'][0]['titleClean'],
            '%channelTitle%' => $search['items'][0]['channelTitle'],
            '%duration%' => $search['items'][0]['duration'],
            '%ptsTime%' => $search['items'][0]['ptsTime'],
            '%size%' => $search['items'][0]['size'],
            '%viewCount%' => $search['items'][0]['viewCount'],
            '%likeCount%' => $search['items'][0]['likeCount'],
            '%dislikeCount%' => $search['items'][0]['dislikeCount'],
            '%publishedAt%' => $search['items'][0]['publishedAt'],
            '%createdAt%' => $search['createdAt'],
            '%slug%' => $slug,
            '%site_name%' => option('site_name'),
            '%domain%' => site_domain()
          ])),
          'robots' => option('search_meta_robots')
        ];

        if (option('cache_search', true) && isset($meta)) {
          $ciCache->save($cacheName, $meta, option('cache_search_expiration_time'));
        }
      }

      if (option('scrape_articles', true)) {
        $article = getArticle($query, option('articles_limit'));
      }

      $data = [
        'title' => $meta['title'],
        'description' => $meta['description'],
        'robots' => $meta['robots'],
        'query' => $query,
        'search' => $search,
        'article' => $article,
        'indexing' =>  $indexing,
        'indexnow' => $indexnow
      ];

      //Check Bot
      if (option('detect_bot') && is_bot($_SERVER['HTTP_USER_AGENT'])) {
        return view(option('bot_theme') . '/search', $data);
      }

      return view(option('theme') . '/search', $data);
    }

    return redirect_to('/');
  }

  public function Download($id)
  {
    $ciCache = \Config\Services::cache();
    $cacheName = md5($id . 'description');

    dmca_block();

    if (option('indexing_auto') == 'true') {
      $indexnow = $this->AutoIndexNow();
      $indexing = $this->AutoIndexing();
    } else {
      $indexnow = 'false';
      $indexing = 'false';
    }

    $download = getVideo($id);

    if (isset($download['id'])) {
      if (!$meta = $ciCache->get($cacheName)) {
        $meta = [
          'title' =>  spintax(strtr(option('download_title', '%site_tagline%'), [
            '%title%' => $download['title'],
            '%titleClean%' => $download['titleClean'],
            '%size%' => $download['size'],
            '%duration%' => $download['duration'],
            '%site_name%' => option('site_name')
          ])),
          'description' =>  spintax(strtr(option('download_meta_description') ? option('download_meta_description') : get_metatext('download'), [
            '%title%' => $download['title'],
            '%titleClean%' => $download['titleClean'],
            '%channelTitle%' => $download['channelTitle'],
            '%duration%' => $download['duration'],
            '%ptsTime%' => $download['duration'],
            '%size%' => $download['size'],
            '%viewCount%' => $download['viewCount'],
            '%likeCount%' => $download['likeCount'],
            '%dislikeCount%' => $download['dislikeCount'],
            '%publishedAt%' => $download['publishedAt'],
            '%createdAt%' => $download['createdAt'],
            '%site_name%' => option('site_name'),
            '%domain%' => site_domain()
          ])),
          'robots' => option('download_meta_robots')
        ];

        if (option('cache_download', true) && isset($meta)) {
          $ciCache->save($cacheName, $meta, option('cache_download_expiration_time'));
        }
      }

      $data = [
        'title' => $meta['title'],
        'description' => $meta['description'],
        'robots' => $meta['robots'],
        'download' => $download,
        'indexing' =>  $indexing,
        'indexnow' => $indexnow
      ];

      //Check Bot
      if (option('detect_bot') && is_bot($_SERVER['HTTP_USER_AGENT'])) {
        return view(option('bot_theme') . '/download', $data);
      }

      return view(option('theme') . '/download', $data);
    }

    return redirect_to('/');
  }

  public function Genre($slug)
  {
    $genreData = option('itunes_genre');

    try {
      $genre = getTopSong(option('itunes_genre_limit'), $genreData[$slug]['id']);

      $meta = [
        'title' =>  spintax(strtr(option('genre_title', '%site_tagline%'), [
          '%title%' => $genreData[$slug]['title'],
          '%site_name%' => option('site_name')
        ])),
        'description' =>  spintax(strtr(option('genre_meta_description'), [
          '%title%' => $genreData[$slug]['title'],
          '%createdAt%' => $genre['createdAt'],
          '%site_name%' => option('site_name'),
          '%domain%' => site_domain()
        ])),
        'robots' => option('genre_meta_robots')
      ];

      $data = [
        'title' => $meta['title'],
        'description' => $meta['description'],
        'robots' => $meta['robots'],
        'genre' => $genre,
        'name' => $genreData[$slug]['title']
      ];

      //Check Bot
      if (option('detect_bot') && is_bot($_SERVER['HTTP_USER_AGENT'])) {
        return view(option('bot_theme') . '/charts/itunes', $data);
      }

      return view(option('theme') . '/charts/itunes', $data);
    } catch (\Throwable $th) {
      return redirect_to('/');
    }
  }

  public function Playlist($slug)
  {
    $playlistData = option('youtube_playlist');

    try {
      $playlist = getPlaylist($playlistData[$slug]['id'], option('youtube_playlist_limit'));

      $meta = [
        'title' =>  spintax(strtr(option('playlist_title', '%site_tagline%'), [
          '%title%' => $playlistData[$slug]['title'],
          '%site_name%' => option('site_name')
        ])),
        'description' =>  spintax(strtr(option('playlist_meta_description'), [
          '%title%' => $playlistData[$slug]['title'],
          '%createdAt%' => $playlist['createdAt'],
          '%site_name%' => option('site_name'),
          '%domain%' => site_domain()
        ])),
        'robots' => option('playlist_meta_robots')
      ];

      $data = [
        'title' => $meta['title'],
        'description' => $meta['description'],
        'robots' => $meta['robots'],
        'playlist' => $playlist,
        'name' => $playlistData[$slug]['title']
      ];

      //Check Bot
      if (option('detect_bot') && is_bot($_SERVER['HTTP_USER_AGENT'])) {
        return view(option('bot_theme') . '/charts/youtube', $data);
      }

      return view(option('theme') . '/charts/youtube', $data);
    } catch (\Throwable $th) {
      return redirect_to('/');
    }
  }

  public function Page($slug)
  {
    $title = ucwords(str_replace(option('permalink_slug_separator'), ' ', $slug));

    $meta = [
      'title' =>  spintax(strtr(option('page_title', '%site_tagline%'), [
        '%title%' => $title,
        '%site_name%' => option('site_name')
      ])),
      'description' =>  spintax(strtr(option('page_meta_description'), [
        '%title%' => $title,
        '%site_name%' => option('site_name'),
        '%domain%' => site_domain()
      ])),
      'robots' => option('page_meta_robots')
    ];

    $data = [
      'title' => $meta['title'],
      'description' => $meta['description'],
      'robots' => $meta['robots'],
    ];

    try {
      //Check Bot
      if (option('detect_bot') && is_bot($_SERVER['HTTP_USER_AGENT'])) {
        return view(option('bot_theme') . '/pages/' . $slug, $data);
      }

      return view(option('theme') . '/pages/' . $slug, $data);
    } catch (\Throwable $th) {
      return redirect_to('/');
    }
  }

  public function Show404()
  {
    $meta = [
      'title' => strtr(option('404_title', '%site_tagline%'), [
        '%site_tagline%' => option('site_tagline'),
        '%site_name%' => option('site_name')
      ]),
      'description' => strtr(option('404_meta_description'), [
        '%site_name%' => option('site_name'),
        '%site_tagline%' => option('site_tagline'),
        '%domain%' => site_domain()
      ]),
      'robots' => option('404_meta_robots')
    ];

    $data = [
      'title' => $meta['title'],
      'description' => $meta['description'],
      'robots' => $meta['robots'],
    ];

    echo view(option('theme') . '/404', $data);
  }

  public function Image($slug)
  {
    try {
      //The cURL stuff...
      $ch = curl_init();
      curl_setopt($ch, CURLOPT_HEADER, 0);
      curl_setopt($ch, CURLOPT_VERBOSE, 0);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
      curl_setopt($ch, CURLOPT_URL, 'https://tse2.mm.bing.net/th?q=' . urlencode(str_replace('-', ' ', $slug)) . '&w=' . $this->request->getVar('w') . '&h=' . $this->request->getVar('h') . '&c=7&rs=1');
      $picture = curl_exec($ch);

      //Display the image in the browser
      $options = [
        'public',
        'max-age'  => 2592000
      ];

      $this->response->setCache($options);
      $this->response->setHeader('Content-Type', 'image/jpeg');
      echo $picture;

      //Close CURL
      curl_close($ch);
    } catch (\Throwable $th) {
      return redirect_to('https://i0.wp.com/3.bp.blogspot.com/-GPUCXsOkt9A/WylLV5Wd2dI/AAAAAAAAAAM/-4sYWZiRKGEvwuFQ9mFaXBsrqyLiwraUACLcBGAs/s1600/largepreview.jpg');
    }
  }

  public function Ping()
  {
    $url = $this->request->getVar('url') ? $this->request->getVar('url') : home_url() . option('sitemap_index_permalink');

    if ($url) {
      $googlesitemap = 'https://www.google.com/webmasters/tools/ping?sitemap=' . $url;
      $bingsitemap = 'https://www.bing.com/webmaster/ping.aspx?siteMap=' . $url;

      $gs = fetch($googlesitemap);
      if ($gs->getStatusCode() == 200) {
        echo 'Success Ping ' . $url . ' to Google';
      }

      $bs = fetch($bingsitemap);
      if ($bs->getStatusCode() == 200) {
        echo ' & Bing';
      }

      return;
    }
  }

  public function FlushCache()
  {
    $cacheDir = \dirname(__DIR__, 2) . "/writable/cache/";
    rmrf($cacheDir);
    mkdir($cacheDir, 0777);
    return 'All Cache deleted..';
  }

  public function AutoIndexNow()
  {
    $key_path = dirname(__DIR__, 2) . '/data/indexnow-api-key.txt';

    if (!file_exists($key_path)) {
      indexnow_reset_key();
    }

    $key = file_get_contents($key_path);
    $keyloc = key_permalink($key);
    $ulrs = array();

    $terms = get_terms(option('indexing_limit'));

    if (count($terms['items']) > 0) {
      foreach ($terms['items'] as $term) {
        $ulrs[] = search_permalink($term);
      }
    }

    $options = [
      "headers" => [
        "Content-Type" => "application/json",
        "Charset" => "utf-8"
      ],
      'json' =>
      [
        'host' => $_SERVER['HTTP_HOST'],
        'key' => $key,
        'keyLocation' => $keyloc,
        'urlList' => $ulrs
      ]
    ];

    $yandex = fetch("https://yandex.com/indexnow", $options, 'POST');
    $bing = fetch("https://www.bing.com/indexnow", $options, 'POST');

    $response = $bing->getStatusCode();
    return $response;
  }

  public function AutoIndexing()
  {
    try {
      $terms = [];
      $googleClient = new Google\Client();
      $json_files = glob(dirname(__DIR__, 2) . '/data/indexing/*.json');

      if ($json_files) {
        $json = $json_files[array_rand($json_files, 1)];
      }

      $googleClient->setAuthConfig($json);
      $googleClient->setScopes(Google_Service_Indexing::INDEXING);
      $googleClient->setUseBatch(true);

      $service = new Google_Service_Indexing($googleClient);
      $batch = $service->createBatch();

      $postBody = new Google_Service_Indexing_UrlNotification();

      $terms = get_terms(option('indexing_limit'));

      if (count($terms['items']) > 0) {
        foreach ($terms['items'] as $term) {
          $postBody->setUrl(search_permalink($term));
          $postBody->setType('URL_UPDATED');
          $batch->add($service->urlNotifications->publish($postBody));
        }

        $results = $batch->execute();

        // foreach ($results as $result) {
        //   echo $result->urlNotificationMetadata->latestUpdate["url"] . "<br/>";
        //   echo $result->urlNotificationMetadata->latestUpdate["notifyTime"] . "<br/>";
        // }

      }

      // echo 'no terms';
    } catch (\Exception $e) {
      // echo 'Caught exception: ',  $e->getMessage(), "<br/>";
    }

    return 200;
  }
}
