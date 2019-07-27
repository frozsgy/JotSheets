<?php
require_once('db.php');
require_once('integrationsBase.php');

class Spreadsheets extends IntegrationsBase
{
    public function __construct()
    {
        parent::__construct('spreadsheets', 'spreadsheet_id');
    }

}
