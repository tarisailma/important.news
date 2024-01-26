<?php

namespace App\Controllers;

class Apis extends BaseController
{
  public function Search()
  {
    $data = [];

    if ($this->request->getVar('query')) {
      $limit = $this->request->getVar('limit') ? $this->request->getVar('limit') : '10';
      $data = getSearch($this->request->getVar('query'), $limit);
      return $this->response->setJSON($data);
    }

    return $this->response->setJSON($data);
  }

  public function Video()
  {
    $data = [];

    if ($this->request->getVar('id')) {
      $data = getVideo($this->request->getVar('id'));
      return $this->response->setJSON($data);
    }

    return $this->response->setJSON($data);
  }

  public function Playlist()
  {
    $data = [];

    if ($this->request->getVar('id')) {
      $limit = $this->request->getVar('limit') ? $this->request->getVar('limit') : '10';
      $data = getPlaylist($this->request->getVar('id'), $limit);
      return $this->response->setJSON($data);
    }

    return $this->response->setJSON($data);
  }

  public function Related()
  {
    $data = [];

    if ($this->request->getVar('id')) {
      $limit = $this->request->getVar('limit') ? $this->request->getVar('limit') : '10';
      $data = getRelated($this->request->getVar('id'), $limit);
      return $this->response->setJSON($data);
    }

    return $this->response->setJSON($data);
  }

  public function Topsong()
  {
    $data = [];

    if ($this->request->getVar('limit')) {
      $genre = $this->request->getVar('genre') ? $this->request->getVar('genre') : false;
      $data = getTopSong($this->request->getVar('limit'), $genre);
      return $this->response->setJSON($data);
    }

    return $this->response->setJSON($data);
  }

  public function Comment()
  {
    $data = [];

    if ($this->request->getVar('id')) {
      $limit = $this->request->getVar('limit') ? $this->request->getVar('limit') : '10';
      $data = getComment($this->request->getVar('id'), $limit);
      return $this->response->setJSON($data);
    }

    return $this->response->setJSON($data);
  }
}
