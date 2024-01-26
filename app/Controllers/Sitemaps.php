<?php

namespace App\Controllers;

use Thepixeldeveloper\Sitemap\Urlset;
use Thepixeldeveloper\Sitemap\Url;
use Thepixeldeveloper\Sitemap\Drivers\XmlWriterDriver;

class Sitemaps extends BaseController
{
  public function Index()
  {
    $urlset = new Urlset();

    $url = new Url(home_url());
    $url->setChangeFreq('daily');
    $url->setPriority('1.0');
    $urlset->add($url);

    $terms = get_terms(option('sitemap_limit'));

    if (count($terms['items']) > 0) {
      foreach ($terms['items'] as $item) {
        $url = new Url(search_permalink($item));
        $url->setChangeFreq('daily');
        $url->setPriority('0.8');
        $urlset->add($url);
      }
    }

    $driver = new XmlWriterDriver();
    $urlset->accept($driver);
    $xml = $driver->output();

    $this->response->setHeader('Content-Type', 'application/xml');
    return $this->response->setBody($xml);
  }

  public function Genre($slug)
  {
    $urlset = new Urlset();

    $url = new Url(home_url());
    $url->setChangeFreq('daily');
    $url->setPriority('1.0');
    $urlset->add($url);

    $genreData = option('itunes_genre');

    try {
      $genre = getTopSong(option('itunes_genre_limit'), $genreData[$slug]['id']);
    } catch (\Throwable $th) {
      $genre = [];
    }

    if (isset($genre['items']) > 0) {
      foreach ($genre['items'] as $item) {
        $url = new Url(search_permalink($item['title']));
        $url->setChangeFreq('daily');
        $url->setPriority('0.8');
        $urlset->add($url);
      }
    }

    $driver = new XmlWriterDriver();
    $urlset->accept($driver);
    $xml = $driver->output();

    $this->response->setHeader('Content-Type', 'application/xml');
    return $this->response->setBody($xml);
  }

  public function Playlist($slug)
  {
    $urlset = new Urlset();

    $url = new Url(home_url());
    $url->setChangeFreq('daily');
    $url->setPriority('1.0');
    $urlset->add($url);

    $playlistData = option('youtube_playlist');

    try {
      $playlist = getPlaylist($playlistData[$slug]['id'], option('youtube_playlist_limit'));
    } catch (\Throwable $th) {
      $playlist = [];
    }

    if (isset($playlist['items']) > 0) {
      foreach ($playlist['items'] as $item) {
        $url = new Url(search_permalink($item['title']));
        $url->setChangeFreq('daily');
        $url->setPriority('0.8');
        $urlset->add($url);
      }
    }

    $driver = new XmlWriterDriver();
    $urlset->accept($driver);
    $xml = $driver->output();

    $this->response->setHeader('Content-Type', 'application/xml');
    return $this->response->setBody($xml);
  }
}
