<?php
require_once('db.php');
require_once('integrationsBase.php');

class Forms extends IntegrationsBase
{

    public function __construct()
    {
        parent::__construct('forms', 'form_id');
    }

}
