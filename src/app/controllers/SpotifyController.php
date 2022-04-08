<?php

use Phalcon\Mvc\Controller;
use GuzzleHttp\Client;


class SpotifyController extends Controller
{
    public function apiAction() {
        $url = "https://accounts.spotify.com/authorize?";

        $client_id = 'bd445785f74f446bab81aacf79d31171';
        $client_secret = '592849a650f44c7b8527347b61ab61e7';
        $headers = [
        'client_id' => $client_id,
        'client_secret' => $client_secret,
        'redirect_uri' => 'http://localhost:8080/spotify/api',
        'scope' => 'playlist-modify-public playlist-read-private playlist-modify-private',
        'response_type' =>'code'
        ];

        $OauthUrl = $url.http_build_query($headers);
        $this->view->OauthUrl = $OauthUrl;
        // die($OauthUrl);
        
        if ($this->request->get('code') !=null){
            $code = $this->request->get('code');
            $data = array(
                'redirect_uri' => 'http://localhost:8080/spotify/api',
                'grant_type'   => 'authorization_code',
                'code'         => $code,
            );
            $ch            = curl_init();
            curl_setopt($ch, CURLOPT_URL, 'https://accounts.spotify.com/api/token');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
            curl_setopt($ch, CURLOPT_HTTPHEADER, array('Authorization: Basic ' . base64_encode($client_id . ':' . $client_secret)));

            $result = json_decode(curl_exec($ch));
            $this->session->token = $result->access_token;
            $this->response->redirect('/spotify/search');
        }


    }
    

    /**
     * Action to search and return songs
     *
     * @return void
     */
    public function searchAction()
    {
        if ($this->request->isPost()) {
            $toSearch = urlencode($this->request->getPost('search'));
            
            if ($this->request->has('track') || count($this->request->getPost()) == 1) {
                $this->view->tracks = $this->result($toSearch, 'track');
            } if ($this->request->has('album')) {
                $this->view->album = $this->result($toSearch, 'album');
            } if ($this->request->has('artist')) {
                $this->view->artist = $this->result($toSearch, 'artist');
            } if ($this->request->has('playlist')) {
                $this->view->playlist = $this->result($toSearch, 'playlist');
            } if ($this->request->has('show')) {
                $this->view->shows = $this->result($toSearch, 'show');
            } if ($this->request->has('episode')) {
                $this->view->episode = $this->result($toSearch, 'episode');
            }
            
        }
    }

    public function addAction() {
        // $user_id = '31nwdvwccxejpkrxbzkbbqdpkrlq';
        // $url = "https://api.spotify.com/v1/playlists//tracks";

        // $ch = curl_init();

        // curl_setopt($ch, CURLOPT_URL,"http://www.example.com/tester.phtml");
        // curl_setopt($ch, CURLOPT_POST, 1);
        // curl_setopt($ch, CURLOPT_POSTFIELDS,
        //             "postvar1=value1&postvar2=value2&postvar3=value3");

        // // In real lif
        // // Receive server response ...
        // curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        // $server_output = curl_exec($ch);

        // curl_close ($ch);
        // // Receive server response ...
        // curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        // $server_output = curl_exec($ch);

        // curl_close ($ch);

    }

    public function result($toSearch, $type) {
        $url = URL . "search?q=$toSearch&type=$type";

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        //  die(print_r($this->session->token));
        $headers = array('Authorization: Bearer ' . $this->session->token);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        // Timeout in seconds
        curl_setopt($ch, CURLOPT_TIMEOUT, 2);

        $result = curl_exec($ch);
        return json_decode($result, true);
    }

}
