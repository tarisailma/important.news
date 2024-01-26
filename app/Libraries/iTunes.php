<?php

namespace App\Libraries;

use Carbon\Carbon;

class iTunes
{
  public static function apiMusicGenre()
  {
    $data = [];
    $url = 'https://itunes.apple.com/WebObjects/MZStoreServices.woa/ws/genres?id=34';
    $response = fetch($url);

    if ($response->getStatusCode() == 200) {
      $json = json_decode($response->getBody(), true);

      if (!empty($json['34']['subgenres'])) {
        foreach ($json['34']['subgenres'] as $subgenre) {
          $data[$subgenre['id']] = $subgenre['name'];

          if (isset($subgenre['subgenres']) && is_array($subgenre['subgenres'])) {
            foreach ($subgenre['subgenres'] as $subsubgenre) {
              $data[$subsubgenre['id']] = $subsubgenre['name'];
            }
          }
        }

        asort($data);

        $data_final = array(
          'items' => $data,
          'createdAt' => Carbon::now()
        );
      }
    }

    return isset($data_final['items']) ? $data_final : $data;
  }

  public static function apiTopsong($options = [])
  {
    $data = [];

    if (!$options['genre']) {
      $url = 'http://ax.itunes.apple.com/WebObjects/MZStoreServices.woa/ws/RSS/topsongs/limit=' . $options['limit'] . '/json';
    } else {
      $url = 'http://itunes.apple.com/us/rss/topsongs/limit=' . $options['limit'] . '/genre=' . $options['genre'] . '/json';
    }

    $response = fetch($url);

    if ($response->getStatusCode() == 200) {
      $json = json_decode($response->getBody(), true);

      if (!empty($json['feed']['entry'])) {
        foreach ($json['feed']['entry'] as $result) {
          $data[] = [
            'id' => $result['id']['attributes']['im:id'],
            'title' => $result['im:artist']['label'] . ' - ' . $result['im:name']['label'],
            'name' => $result['im:name']['label'],
            'artist' => $result['im:artist']['label'],
            'image' => $result['im:image'][count($result['im:image']) - 1]['label'],
            'genre' => $result['category']['attributes']['label'],
            'album' => (isset($result['im:collection']['im:name']['label']) ? $result['im:collection']['im:name']['label'] : '-'),
            'dateRelease' => isset($result['im:releaseDate']['attributes']['label']) ? Carbon::parse($result['im:releaseDate']['attributes']['label']) : Carbon::now(),
          ];
        }

        $data_final = array(
          'items' => $data,
          'createdAt' => Carbon::now()
        );
      }
    }

    return isset($data_final['items']) ? $data_final : $data;
  }
}
