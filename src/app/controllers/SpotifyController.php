<?php

use Phalcon\Mvc\Controller;
use GuzzleHttp\Client;

/**
 * Controller class that handles all spotify related actions
 */
class SpotifyController extends Controller
{
    /**
     * Action to generate and set Access Token in session variable
     *
     * @return void
     */
    public function apiAction() {
        /* Generating Code */
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
        /////////////////////////////////////////////

        /* if code is set then generate and set the access token into session variable */

        if ($this->request->get('code') !=null){
            $code = $this->request->get('code');
            $data = array(
                'redirect_uri' => 'http://localhost:8080/spotify/api',
                'grant_type'   => 'authorization_code',
                'code'         => $code,
            );

            $ch = curl_init();

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
     * Action to search and return tracks, albums, playlists, shows and episodes.
     *
     * @return void
     */
    public function searchAction()
    {
        $this->view->allPlaylists = $this->getPlaylist();
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
    /**
     * Action to add Tracks to a playlist
     *
     * @return void
     */
    public function addAction() {
        
        if ($this->request->isPost()) {
            $playlist_id = $this->request->getPost('playlist');
            $uri = $this->request->getPost('uri');

            $url = "https://api.spotify.com/";

            $client = new Client([
                'base_uri' => URL
            ]);
            $result = $client->request('POST', "/v1/playlists/$playlist_id/tracks?uris=$uri", [
                'headers' => [
                    'Authorization' => "Bearer " . $this->session->token
                ]
            ]);
        }
        $this->response->redirect('/spotify/search');

    }

    /**
     * Function that accepts the search query and 
     *
     * @param [type] $toSearch
     * @param [type] $type
     * @return void
     */
    public function result($toSearch, $type) {
        $url = URL . "search?q=$toSearch&type=$type";

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);

        $headers = array('Authorization: Bearer ' . $this->session->token);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        // Timeout in seconds
        curl_setopt($ch, CURLOPT_TIMEOUT, 2);

        $result = curl_exec($ch);
        return json_decode($result, true);
    }

    /**
     * Function to return current user's playlists
     *
     * @return void
     */
    public function getPlaylist() {
        $url = "https://api.spotify.com/v1/me/playlists";

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);

        $headers = array('Authorization: Bearer ' . $this->session->token);
        
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        // Timeout in seconds
        curl_setopt($ch, CURLOPT_TIMEOUT, 2);

        $result = curl_exec($ch);
        return json_decode($result, true);

    }

    /**
     * Action to delete a track from playlist
     *
     * @return void
     */
    public function removeTrackAction() {
        if ($this->request->isPost()) {
            $playlist_id = $this->request->getPost('playlist_id');
            $track_uri = $this->request->getPost('removeTrack');

            

            $client = new Client([
                'base_uri' => URL
            ]);
            $result = $client->request('DELETE', "v1/playlists/$playlist_id/tracks", [
                'headers' => [
                    'Authorization' => "Bearer " . $this->session->token
                ],
                'body' => json_encode([
                    "uris" => [$track_uri]
                ])
            ]);
            
        }
        $this->response->redirect('/spotify/viewPlaylists?id='.$playlist_id);
    }

    /**
     * Action to Create Playlist
     *
     * @return void
     */
    public function createPlaylistAction() {
        if ($this->request->isPost()){
            $playlistName = $this->request->getPost('playlist');
            $description = $this->request->getPost('description');
            $user_id = $this->getUserDetails()['id'];

            $client = new Client([
                'base_uri' => URL
            ]);
            $result = $client->request('POST', "https://api.spotify.com/v1/users/$user_id/playlists", [
                'headers' => [
                    'Authorization' => "Bearer " . $this->session->token
                ],
                'body' => json_encode([
                    "name" => $playlistName,
                    "description" => $description,
                    "public" => false
                ])
            ]);
       }
       $this->response->redirect('/spotify/search');
    }

    /**
     * Get Current User's Details
     *
     * @return void
     */
    public function getUserDetails() {
        $url = URL.'me';

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);

        $headers = array('Authorization: Bearer ' . $this->session->token);
        
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        // Timeout in seconds
        curl_setopt($ch, CURLOPT_TIMEOUT, 2);

        $result = curl_exec($ch);
        
        return (json_decode($result, true));
    }

    /**
     * Action to display all playlists of the current user
     *
     * @return void
     */
    public function viewPlaylistsAction () {
        if ($this->request->get('id')!=null) {
            $playlist_id = $this->request->get('id');
            $url = URL."/$playlist_id/tracks";

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);

            $headers = array('Authorization: Bearer ' . $this->session->token);
            
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

            // Timeout in seconds
            curl_setopt($ch, CURLOPT_TIMEOUT, 2);

            $result = curl_exec($ch);
            $this->view->playlists = json_decode($result, true);
            
        }
    }
}
