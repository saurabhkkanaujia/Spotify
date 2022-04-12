<?php

use Phalcon\Mvc\Controller;

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
                    $this->response->redirect('/spotify/dashboard');
                }
            }
        }
    }
}
