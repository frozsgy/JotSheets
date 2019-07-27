<?php
require_once('db.php');


class Field
{
    private $token_id;

    public function __construct($token_id)
    {
        $token_id = DB::$c->real_escape_string($token_id);
        $this->token_id = $token_id;
    }

    public function addField($column, $field)
    {
        $column = DB::$c->real_escape_string($column);
        $field = DB::$c->real_escape_string($field);
        $sql = "INSERT into `fields` VALUES (null, '$this->token_id', '$column', '$field');";
        DB::$c->query($sql);
        return DB::$c->insert_id;
    }

    public function getFields()
    {
        $fields = array();
        $sql = "SELECT * from `fields` WHERE `token_id` = '$this->token_id' ORDER by `col`";
        $r = DB::$c->query($sql);
        while($p = $r->fetch_assoc()) {
            array_push($fields, $p['form_field']);
        }
        return $fields;
    }


}
