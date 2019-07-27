<?php
require_once('db.php');

abstract class IntegrationsBase
{

    protected $tableName;
    protected $fieldName;

    public function __construct($tableName, $fieldName)
    {
        $this->tableName = $tableName;
        $this->fieldName = $fieldName;
    }

    public function saveID($id)
    {
        if ($this->checkID($id)) {
            return $this->getID($id);
        } else {
            return $this->addID($id);
        }
    }

    public function checkID($id)
    {
        $id = DB::$c->real_escape_string($id);
        $sql = "SELECT * from  `$this->tableName` WHERE `$this->fieldName` = '$id'";
        $cr = DB::$c->query($sql);
        return ($cr->num_rows == 1);
    }

    public function getID($id)
    {
        $id = DB::$c->real_escape_string($id);
        $sql = "SELECT * from  `$this->tableName` WHERE `$this->fieldName` = '$id'";
        $cr = DB::$c->query($sql);
        return $cr->fetch_assoc()['id'];
    }

    protected function addID($id)
    {
        $id = DB::$c->real_escape_string($id);
        $sql = "INSERT into `$this->tableName` VALUES (null, '$id');";
        DB::$c->query($sql);
        return $this->getID($id);
    }

    public function getDataFromID($id)
    {
        $id = DB::$c->real_escape_string($id);
        $sql = "SELECT * from  `$this->tableName` WHERE `id` = '$id'";
        $cr = DB::$c->query($sql);
        if ($cr->num_rows == 1) {
            return $cr->fetch_assoc()[$this->fieldName];
        }
    }

}
