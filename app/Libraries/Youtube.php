<?php

namespace App\Libraries;

use Carbon\Carbon;

class Youtube
{
  public static function apiSearch($options = [])
  {
    $data = [];
    $url = 'https://www.googleapis.com/youtube/v3/search?part=snippet&q=' . urlencode($options['query']) . '&type=video&regionCode=US&maxResults=' . $options['limit'] . '&key=' . random_pick(option('youtube_api_keys'));
    $response = fetch($url);

    if ($response->getStatusCode() == 200) {
      $json = json_decode($response->getBody(), true);

      if (!empty($json['items'])) {
        foreach ($json['items'] as $item)
          $video_ids[] = $item['id']['videoId'];

        unset($item);

        $url_detail = 'https://www.googleapis.com/youtube/v3/videos?part=snippet,contentDetails,statistics&id=' . implode(',', $video_ids) . '&key=' . random_pick(option('youtube_api_keys'));

        $response_detail = fetch($url_detail);

        if ($response_detail->getStatusCode() == 200) {
          $json_detail = json_decode($response_detail->getBody(), true);

          foreach ($json_detail['items'] as $item) {
            $snippet = $item['snippet'];
            $content_details = $item['contentDetails'];
            $statistics = $item['statistics'];

            $item_data['id'] = $item['id'];
            $item_data['title'] = preg_replace('/["?]/', '', $snippet['title']);
            $item_data['titleClean'] = clean_title($item_data['title']);
            $item_data['description'] = remove_http($snippet['description']);
            $item_data['thumbnails'] = $snippet['thumbnails'];
            $item_data['channelId'] = $item['snippet']['channelId'];
            $item_data['channelTitle'] = $snippet['channelTitle'];

            $duration = convert_youtube_time($content_details['duration']);
            $exp_duration = explode(':', $duration);

            if (count($exp_duration) == 2) {
              $parsed = date_parse('00:' . $duration);
              $seconds = ($parsed['minute'] * 60) + $parsed['second'];
            } else {
              $parsed = date_parse($duration);
              $seconds = ($parsed['hour'] * 60 * 60) + ($parsed['minute'] * 60) + $parsed['second'];
            }

            $item_data['duration'] = $duration;
            $item_data['ptsTime'] = $content_details['duration'];
            $item_data['size'] = format_bytes(($seconds * (192 / 8) * 1000));
            $item_data['second'] = $seconds;
            $item_data['viewCount'] = isset($statistics['viewCount']) ? number_format($statistics['viewCount']) : 0;
            $item_data['likeCount'] = isset($statistics['likeCount']) ? number_format($statistics['likeCount']) : 0;
            $item_data['dislikeCount'] = isset($statistics['dislikeCount']) ? number_format($statistics['dislikeCount']) : 0;
            $item_data['publishedAt'] = Carbon::parse($snippet['publishedAt']);
            $item_data['tags'] = isset($snippet['tags']) ? $snippet['tags'] : [];

            $data[] = $item_data;
          }

          if (option('shuffle_yt_data') == true) {
            $data = shuffle_exclude($data, option('shuffle_index_exclude'));
          }

          $data_final = array(
            'id' => $data[0]['id'],
            'title' => $data[0]['title'],
            'titleClean' => $data[0]['titleClean'],
            'description' => $data[0]['description'],
            'thumbnails' => $data[0]['thumbnails'],
            'channelId' => $data[0]['channelId'],
            'channelTitle' => $data[0]['channelTitle'],
            'duration' => $data[0]['duration'],
            'ptsTime' => $data[0]['ptsTime'],
            'size' => $data[0]['size'],
            'second' => $data[0]['second'],
            'viewCount' => $data[0]['viewCount'],
            'likeCount' => $data[0]['likeCount'],
            'dislikeCount' => $data[0]['dislikeCount'],
            'publishedAt' => $data[0]['publishedAt'],
            'tags' => $data[0]['tags'],
            'lyric' => getLyric($data[0]['description']),
            'items' => $data,
            'createdAt' => Carbon::now()
          );
        }
      }
    }

    return isset($data_final['items']) ? $data_final : $data;
  }

  public static function apiRelated($options = [])
  {
    $data = [];
    $url = 'https://www.googleapis.com/youtube/v3/search?part=snippet&type=video&regionCode=US&maxResults=' . $options['limit'] . '&relatedToVideoId=' . $options['id'] . '&key=' . random_pick(option('youtube_api_keys'));
    $response = fetch($url);

    if ($response->getStatusCode() == 200) {
      $json = json_decode($response->getBody(), true);


      if (!empty($json['items'])) {
        foreach ($json['items'] as $item)
          $video_ids[] = $item['id']['videoId'];

        unset($item);

        $url_detail = 'https://www.googleapis.com/youtube/v3/videos?part=snippet,contentDetails,statistics&id=' . implode(',', $video_ids) . '&key=' . random_pick(option('youtube_api_keys'));
        $response_detail = fetch($url_detail);

        if ($response_detail->getStatusCode() == 200) {
          $json_detail = json_decode($response_detail->getBody(), true);

          foreach ($json_detail['items'] as $item) {
            $snippet = $item['snippet'];
            $content_details = $item['contentDetails'];
            $statistics = $item['statistics'];

            $item_data['id'] = $item['id'];
            $item_data['title'] = preg_replace('/["?]/', '', $snippet['title']);
            $item_data['titleClean'] = clean_title($item_data['title']);
            $item_data['description'] = remove_http($snippet['description']);
            $item_data['thumbnails'] = $snippet['thumbnails'];
            $item_data['channelId'] = $item['snippet']['channelId'];
            $item_data['channelTitle'] = $snippet['channelTitle'];

            $duration = convert_youtube_time($content_details['duration']);
            $exp_duration = explode(':', $duration);

            if (count($exp_duration) == 2) {
              $parsed = date_parse('00:' . $duration);
              $seconds = ($parsed['minute'] * 60) + $parsed['second'];
            } else {
              $parsed = date_parse($duration);
              $seconds = ($parsed['hour'] * 60 * 60) + ($parsed['minute'] * 60) + $parsed['second'];
            }

            $item_data['duration'] = $duration;
            $item_data['ptsTime'] = $content_details['duration'];
            $item_data['size'] = format_bytes(($seconds * (192 / 8) * 1000));
            $item_data['second'] = $seconds;
            $item_data['viewCount'] = isset($statistics['viewCount']) ? number_format($statistics['viewCount']) : 0;
            $item_data['likeCount'] = isset($statistics['likeCount']) ? number_format($statistics['likeCount']) : 0;
            $item_data['dislikeCount'] = isset($statistics['dislikeCount']) ? number_format($statistics['dislikeCount']) : 0;
            $item_data['publishedAt'] = Carbon::parse($snippet['publishedAt']);
            $item_data['tags'] = isset($snippet['tags']) ? $snippet['tags'] : [];

            $data[] = $item_data;
          }

          $data_final = array(
            'items' => $data,
            'createdAt' => Carbon::now()
          );
        }
      }
    }

    return isset($data_final['items']) ? $data_final : $data;
  }

  public static function apiVideo($options = [])
  {
    $data = [];
    $url = 'https://www.googleapis.com/youtube/v3/videos?part=snippet,contentDetails,statistics&id=' . $options['id'] . '&key=' . random_pick(option('youtube_api_keys'));
    $response = fetch($url);

    if ($response->getStatusCode() == 200) {
      $json = json_decode($response->getBody(), true);

      if (isset($json['items'][0])) {
        $snippet = $json['items'][0]['snippet'];
        $content_details = $json['items'][0]['contentDetails'];
        $statistics = $json['items'][0]['statistics'];
        $item = $json['items'][0];

        $data['id'] = $item['id'];
        $data['title'] = preg_replace('/["?]/', '', $snippet['title']);
        $data['titleClean'] = clean_title($data['title']);
        $data['description'] = remove_http($snippet['description']);
        $data['thumbnails'] = $snippet['thumbnails'];
        $data['channelId'] = $item['snippet']['channelId'];
        $data['channelTitle'] = $snippet['channelTitle'];

        $duration = convert_youtube_time($content_details['duration']);
        $exp_duration = explode(':', $duration);

        if (count($exp_duration) == 2) {
          $parsed = date_parse('00:' . $duration);
          $seconds = ($parsed['minute'] * 60) + $parsed['second'];
        } else {
          $parsed = date_parse($duration);
          $seconds = ($parsed['hour'] * 60 * 60) + ($parsed['minute'] * 60) + $parsed['second'];
        }

        $data['duration'] = $duration;
        $data['ptsTime'] = $content_details['duration'];
        $data['size'] = format_bytes(($seconds * (192 / 8) * 1000));
        $data['second'] = $seconds;
        $data['viewCount'] = isset($statistics['viewCount']) ? number_format($statistics['viewCount']) : 0;
        $data['likeCount'] = isset($statistics['likeCount']) ? number_format($statistics['likeCount']) : 0;
        $data['dislikeCount'] = isset($statistics['dislikeCount']) ? number_format($statistics['dislikeCount']) : 0;
        $data['publishedAt'] = Carbon::parse($snippet['publishedAt']);
        $data['tags'] = isset($snippet['tags']) ? $snippet['tags'] : [];
        $data['createdAt'] = Carbon::now();
      }
    }

    return $data;
  }

  public static function apiPlaylist($options = [])
  {
    $data = [];
    $url = 'https://www.googleapis.com/youtube/v3/playlistItems?part=snippet,id&maxResults=' . $options['limit'] . '&playlistId=' . $options['id'] . '&key=' . random_pick(option('youtube_api_keys'));
    $response = fetch($url);

    if ($response->getStatusCode() == 200) {
      $json = json_decode($response->getBody(), true);

      if (!empty($json['items'])) {
        foreach ($json['items'] as $item)
          $video_ids[] = $item['snippet']['resourceId']['videoId'];

        unset($item);

        $url_detail = 'https://www.googleapis.com/youtube/v3/videos?part=snippet,contentDetails,statistics&id=' . implode(',', $video_ids) . '&key=' . random_pick(option('youtube_api_keys'));
        $response_detail = fetch($url_detail);

        if ($response_detail->getStatusCode() == 200) {
          $json_detail = json_decode($response_detail->getBody(), true);

          foreach ($json_detail['items'] as $item) {
            $snippet = $item['snippet'];
            $content_details = $item['contentDetails'];
            $statistics = $item['statistics'];

            $item_data['id'] = $item['id'];
            $item_data['title'] = preg_replace('/["?]/', '', $snippet['title']);
            $item_data['titleClean'] = clean_title($item_data['title']);
            $item_data['description'] = remove_http($snippet['description']);
            $item_data['thumbnails'] = $snippet['thumbnails'];
            $item_data['channelId'] = $item['snippet']['channelId'];
            $item_data['channelTitle'] = $snippet['channelTitle'];

            $duration = convert_youtube_time($content_details['duration']);
            $exp_duration = explode(':', $duration);

            if (count($exp_duration) == 2) {
              $parsed = date_parse('00:' . $duration);
              $seconds = ($parsed['minute'] * 60) + $parsed['second'];
            } else {
              $parsed = date_parse($duration);
              $seconds = ($parsed['hour'] * 60 * 60) + ($parsed['minute'] * 60) + $parsed['second'];
            }

            $item_data['duration'] = $duration;
            $item_data['ptsTime'] = $content_details['duration'];
            $item_data['size'] = format_bytes(($seconds * (192 / 8) * 1000));
            $item_data['second'] = $seconds;
            $item_data['viewCount'] = isset($statistics['viewCount']) ? number_format($statistics['viewCount']) : 0;
            $item_data['likeCount'] = isset($statistics['likeCount']) ? number_format($statistics['likeCount']) : 0;
            $item_data['dislikeCount'] = isset($statistics['dislikeCount']) ? number_format($statistics['dislikeCount']) : 0;
            $item_data['publishedAt'] = Carbon::parse($snippet['publishedAt']);
            $item_data['tags'] = isset($snippet['tags']) ? $snippet['tags'] : [];

            $data[] = $item_data;
          }

          $data_final = array(
            'items' => $data,
            'createdAt' => Carbon::now()
          );
        }
      }
    }

    return isset($data_final['items']) ? $data_final : $data;
  }

  public static function apiComment($options = [])
  {
    $data = [];
    $api_comments = 'https://www.googleapis.com/youtube/v3/commentThreads?part=snippet&maxResults=' . $options['limit'] . '&videoId=' . $options['id'] . '&key=' . random_pick(option('youtube_api_keys'));
    $response = fetch($api_comments);

    if ($response->getStatusCode() == 200) {
      $json = json_decode($response->getBody(), true);

      foreach ($json['items'] as $item) {
        $snippet = $item['snippet']['topLevelComment']['snippet'];

        $item_data['videoId'] = $snippet['videoId'];
        $item_data['authorDisplayName'] = $snippet['authorDisplayName'];
        $item_data['authorProfileImageUrl'] = $snippet['authorProfileImageUrl'];
        $item_data['textDisplay'] = $snippet['textDisplay'];
        $item_data['likeCount'] = $snippet['likeCount'];
        $item_data['publishedAt'] = Carbon::parse($snippet['publishedAt']);

        $data[] = $item_data;
      }

      $data_final = array(
        'items' => $data,
        'createdAt' => Carbon::now()
      );
    }

    return isset($data_final['items']) ? $data_final : $data;
  }
}
