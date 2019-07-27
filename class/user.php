<?php
require_once('db.php');

class User
{
    private $uid;
    private $gid;
    private $token;

    public function __construct($uid = '', $gid = '', $token = '')
    {
        $this->uid = $uid;
        $this->gid = $gid;
        $this->token = $token;
        if ($this->checkGID($gid) && !empty($token)) {
            $this->updateUserToken($gid, $token['access_token']);
        } elseif (!empty($token)) {
            $this->addUser($gid, $token['access_token'], $token['refresh_token']);
        }
    }

    private function checkGID($gid)
    {
        $gid = DB::$c->real_escape_string($gid);
        $sql = "SELECT * from  `users` WHERE `google_id` = '$gid'";
        $cr = DB::$c->query($sql);
        return ($cr->num_rows == 1);
    }

    private function addUser($gid, $access_token, $refresh_token)
    {
        $gid = DB::$c->real_escape_string($gid);
        $access_token = DB::$c->real_escape_string($access_token);
        $refresh_token = DB::$c->real_escape_string($refresh_token);
        $sql = "INSERT into `users` VALUES (null, '$gid', '$access_token', '$refresh_token', null);";
        DB::$c->query($sql);
    }

    public function updateUserToken($gid, $access_token)
    {
        $gid = DB::$c->real_escape_string($gid);
        $access_token = DB::$c->real_escape_string($access_token);
        $sql = "UPDATE `users` SET `token` = '$access_token' WHERE `google_id` = '$gid';";
        $r = DB::$c->query($sql);
    }

    public function getRefreshToken($gid)
    {
        $gid = DB::$c->real_escape_string($gid);
        $sql = "SELECT * from  `users` WHERE `google_id` = '$gid'";
        $cr = DB::$c->query($sql);
        return $cr->fetch_assoc()['refresh_token'];
    }

    public function getGIDfromUID()
    {
        $uid = DB::$c->real_escape_string($this->uid);
        $sql = "SELECT * from  `users` WHERE `id` = '$uid'";
        $cr = DB::$c->query($sql);
        return $cr->fetch_assoc()['google_id'];
    }

    public function getUIDfromGID($gid)
    {
        $uid = DB::$c->real_escape_string($gid);
        $sql = "SELECT * from  `users` WHERE `google_id` = '$gid'";
        $cr = DB::$c->query($sql);
        return $cr->fetch_assoc()['id'];
    }

    public function getJotFormToken($gid)
    {
        $gid = DB::$c->real_escape_string($gid);
        $sql = "SELECT * from  `users` WHERE `google_id` = '$gid'";
        $cr = DB::$c->query($sql);
        return $cr->fetch_assoc()['jf_token'];
    }

    public function setJotFormToken($gid, $token)
    {
        $gid = DB::$c->real_escape_string($gid);
        $access_token = DB::$c->real_escape_string($token);
        $sql = "UPDATE `users` SET `jf_token` = '$token' WHERE `google_id` = '$gid';";
        $r = DB::$c->query($sql);
    }

}
