<?php

namespace App\Listeners;

use Phalcon\Url;
use OrderController;
use Orders;
use Phalcon\Di\Injectable;
use Phalcon\Events\Event;
use Products;
use ProductsController;
use Settings;
use SpotifyController;
use Users;

class NotificationsListeners extends Injectable
{
    public function refreshToken(Event $event, SpotifyController $component) {
        $user = Users::findFirst($this->session->loginUser->id);
        $refresh_token = $user->refresh_token;
        $client_id = 'bd445785f74f446bab81aacf79d31171';
        $client_secret = '592849a650f44c7b8527347b61ab61e7';
        $data = array(
            'redirect_uri' => 'http://localhost:8080/spotify/api',
            'grant_type'   => 'refresh_token',
            'refresh_token' => $refresh_token
        );

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, 'https://accounts.spotify.com/api/token');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Authorization: Basic ' . base64_encode($client_id . ':' . $client_secret)));

        $result = json_decode(curl_exec($ch));
        $user = Users::findFirst($this->session->loginUser->id);
        $user->access_token = $result->access_token;
        $this->session->token = $result->access_token;
        $user->update();
    }


}
