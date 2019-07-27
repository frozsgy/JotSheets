<?php

class DB
{
    public static $c;
}

$server = '';
$un = '';
$pw = '';
$db = '';

DB::$c = new mysqli($server, $un, $pw, $db);
if (DB::$c->connect_error) {
    die("Check DB: $c->connect_error");
}
if (!DB::$c->set_charset("utf8")) {
    die("DB Error: $c->error");
}
