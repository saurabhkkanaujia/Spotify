<?php

use Phalcon\Mvc\Controller;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;

class UserController extends Controller
{
    public function signupAction()
    {
        if ($this->request->isPost()) {
            
            $user = new Users();
            $obj = new App\Components\Myescaper();

            $inputData = array(
                'username' => $obj->sanitize($this->request->getPost('username')),
                'email' => $obj->sanitize($this->request->getPost('email')),
                'password' => $obj->sanitize($this->request->getPost('password')),
                'password2' => $obj->sanitize($this->request->getPost('password2'))
            );
            if($inputData['password'] == $inputData['password2']){

                $user->assign(
                    $inputData,
                    [
                        'name',
                        'username',
                        'email',
                        'password'
                    ]
                );
    
                $success = $user->save();
    
                $this->view->success = $success;
    
                if ($success) {
                    $this->response->redirect('user/signin');
                    
                    $this->view->message = "Register succesfully";
                } else {
                    $this->view->message = "Not Register succesfully due to following reason: <br>" . implode("<br>", $user->getMessages());
                }
            } else {
                $this->view->message = "Password and confirm password do not match";
            }

        }
    }

    public function signinAction()
    {
        if ($this->request->isPost()) {
            $postData = $this->request->getPost();
            
            $user = Users::find([
                'conditions' => 'email= :email: AND password = :password:',
                'bind' => [
                    'email' => $postData['email'],
                    'password' => $postData['password'],
                ]
            ]);
            if (count($user) == 0) {
                $this->response->redirect('user/signin?err="Invalid Credentials');
                    
            } else {
                $user = json_decode(json_encode($user[0]));
                $this->session->loginUser = json_decode(json_encode($user));
                
                $access_token = $user->access_token;
                if(!$access_token) {
                    $this->response->redirect('/spotify/api');
                } else {
                    $this->response->redirect('/user/dashboard');
                }
            }
        }
    }
    public function dashboardAction() {
        if($this->session->loginUser!=null){
            try{
                $user = new SpotifyController;
                $this->view->user = $user->getUserDetails();

                $user = Users::findFirst($this->session->loginUser->id);
                $access_token = $user->access_token;
        
                $client = new Client([
                    'base_uri' => URL
                ]);
                $result = $client->request('GET', "recommendations?seed_artists=4NHQUGzhtTLFvgF5SZesLK&seed_tracks=0c6xIDDpzE81m2q797ordA&seed_genres=classical", [
                    'headers' => [
                        'Authorization' => "Bearer " . $access_token
                    ]
                ]);
                $this->view->data = json_decode($result->getBody(), true);
            } catch (ClientException $e) {
                $eventsManager = $this->di->get('EventsManager');
                $eventsManager->fire('notifications:refreshToken', $this);
            }
        } else {
            $this->response->redirect('/user/signin?err=Please Sign in First');
        }
        
    }
}
