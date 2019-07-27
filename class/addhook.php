<?php

require_once('class/add.php');

class AddHook extends AddBase
{

    public function __construct($google, $user, $jotform)
    {
        parent::__construct($google, $user, $jotform);
        $this->dest = 'add.php';
        $this->view->set('dest', $this->dest);
        $this->view->set('token', $this->token);
    }

}
