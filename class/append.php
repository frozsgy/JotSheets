<?php

require_once('class/add.php');

class AppendHook extends AddBase
{

    public function __construct($google, $user, $jotform, $token)
    {
        parent::__construct($google, $user, $jotform);
        $this->dest = 'append.php';
        $this->token = $token;
        $this->view->set('dest', $this->dest);
        $this->view->set('token', $this->token);
    }

}
