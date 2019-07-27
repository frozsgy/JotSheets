<?php
require_once('db.php');
require_once('integrationsBase.php');

class Unique extends IntegrationsBase
{

    public function __construct()
    {
        parent::__construct('tokens', 'token');
    }

    public function getFID($id)
    {
        $id = DB::$c->real_escape_string($id);
        $sql = "SELECT * from  `$this->tableName` WHERE `$this->fieldName` = '$id'";
        $cr = DB::$c->query($sql);
        return $cr->fetch_assoc()['form_id'];
    }

    public function getFIDbyID($id)
    {
        $id = DB::$c->real_escape_string($id);
        $sql = "SELECT * from  `$this->tableName` WHERE `id` = '$id'";
        $cr = DB::$c->query($sql);
        return $cr->fetch_assoc()['form_id'];
    }

    public function getUID($id)
    {
        $id = DB::$c->real_escape_string($id);
        $sql = "SELECT * from  `$this->tableName` WHERE `$this->fieldName` = '$id'";
        $cr = DB::$c->query($sql);
        return $cr->fetch_assoc()['user_id'];
    }

    public function getUIDbyID($id)
    {
        $id = DB::$c->real_escape_string($id);
        $sql = "SELECT * from  `$this->tableName` WHERE `id` = '$id'";
        $cr = DB::$c->query($sql);
        return $cr->fetch_assoc()['user_id'];
    }

    public function getSpreadsheets($token)
    {
        $token_id = $this->getID($token);
        return $this->getSpreadsheetsbyID($token_id);
    }

    public function getSpreadsheetsbyID($token_id)
    {
        $token_id = DB::$c->real_escape_string($token_id);
        $tsql = "SELECT DISTINCT `spreadsheet_id` from `hooks` WHERE `token` = '$token_id'";
        $rt = DB::$c->query($tsql);
        $result = array();
        while ($pt = $rt->fetch_assoc()) {
            $sid = $pt['spreadsheet_id'];
            $ssql = "SELECT `sheet` from `hooks` WHERE `token` = '$token_id' and `spreadsheet_id` = '$sid'";
            $rs = DB::$c->query($ssql);
            $pages = array();
            while ($ps = $rs->fetch_assoc()) {
                array_push($pages, $ps['sheet']);
            }
            $result[$sid] = $pages;
        }
        return $result;
    }

    public function getUniqueIDs($id)
    {
        $id = DB::$c->real_escape_string($id);
        $sql = "SELECT * from  `$this->tableName` WHERE `$this->fieldName` = '$id'";
        $cr = DB::$c->query($sql);
        $result = array();
        while ($p = $cr->fetch_assoc()) {
            array_push($result, $p['id']);
        }
        return $result;
    }
}
