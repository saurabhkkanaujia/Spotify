<?php

use Phalcon\Mvc\Controller;


class IndexController extends Controller
{
    /**
     * Action redirects to the search action of spotify controller
     *
     * @return void
     */
    public function indexAction()
    {
        $this->response->redirect('/user/signin');
    }
}