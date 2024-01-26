<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use Google;
use Google_Service_Indexing;
use Google_Service_Indexing_UrlNotification;

class Indexing extends BaseController
{
    public function Index()
    {
        if ($this->request->getVar('type') == 'indexnow') {
            return $this->IndexNow();
        }

        if ($this->request->getVar('type') == 'reset') {
            return $this->IndexNowReset();
        }

        return $this->GoogleIndexing();
    }
    public function GoogleIndexing()
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

                foreach ($results as $result) {
                    echo $result->urlNotificationMetadata->latestUpdate["url"] . "<br/>";
                    echo $result->urlNotificationMetadata->latestUpdate["notifyTime"] . "<br/>";
                }
            }

            echo 'no terms';
        } catch (\Exception $e) {
            echo 'Caught exception: ',  $e->getMessage(), "<br/>";
        }

        return;
    }

    public function IndexNowKey($id)
    {
        $this->response->setHeader('Content-Type', 'text/plai');
        return $this->response->setBody($id);
    }

    public function IndexNow()
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

        echo 'Indexnow Microsoft Bing Status: ';
        echo $bing->getStatusCode();
        echo '<br/> Indexnow Yandex Status: ';
        echo $yandex->getStatusCode();
        echo '<br/>Index Key : ' . $key;
        echo '<br/>Index keyLocation : ' . $keyloc;
        echo '<pre>Post Terms: <br/>';
        print_r(implode("<br>", $ulrs));
        echo '</pre>';

        return;
    }

    public function IndexNowReset()
    {
        $key = indexnow_reset_key();
        $this->response->setHeader('Content-Type', 'text/plai');
        return $this->response->setBody($key);
    }
}
