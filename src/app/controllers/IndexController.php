<?php

use Phalcon\Mvc\Controller;


class IndexController extends Controller
{
    /**
     * Action redirects to the signin page
     *
     * @return void
     */
    public function indexAction()
    {
        $this->response->redirect('/user/signin');
    }
}