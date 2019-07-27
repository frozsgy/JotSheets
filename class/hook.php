<?php
require_once('db.php');
require_once('integrations.php');
require_once('PushId.php');
require_once('views/engine.php');

class Hook
{
    private $uid;

    public function __construct($uid)
    {
        $uid = DB::$c->real_escape_string($uid);
        $this->uid = $uid;
    }

    public function getCount()
    {
        $sql = "SELECT `id` from `tokens` WHERE `user_id` = '$this->uid' and `enabled` = '1'";
        $r = DB::$c->query($sql);
        return $r->num_rows;
    }

    public function printWebhooks()
    {
        $view = new Views('empty');
        if ($this->getCount()) {
            $webhooks = $this->listWebhooks();
            $view->loadAndRender('webhooks/header');
            foreach ($webhooks as $hook) {
                $view->loadFile('webhooks/token');
                $view->set('token', $hook[0]);
                $view->set('jf_id', $hook[1]);
                $view->set('jf_title', $hook[2]);
                $view->render();
                foreach ($hook[3] as $sheet) {
                    $spreadsheet = $sheet[0];
                    $view->loadFile('webhooks/spreadsheets_header');
                    $view->set('spreadsheet_name', $spreadsheet);
                    $view->render();
                    $sheets = $sheet[1];
                    foreach ($sheets as $sheetName) {
                        $view->loadFile('webhooks/sheets');
                        $view->set('sheet_name', $sheetName);
                        $view->render();
                    }
                    $view->loadAndRender('webhooks/spreadsheets_footer');
                }

            }
            $view->loadAndRender('webhooks/footer');
        } else {
            $view->loadAndRender('main/no');
        }
    }

    public function listWebhooks()
    {
        if ($this->getCount()) {
            $result = array();
            $sql = "SELECT * from `tokens` WHERE `user_id` = '$this->uid' and `enabled` = '1' GROUP by `token`";
            $r = DB::$c->query($sql);
            $f = new Forms();
            $s = new Spreadsheets();
            $g = new Google('', 'reauth', $_SESSION['token']);
            $gid = $g->getGID();
            $u = new User();
            $uid = $u->getUIDfromGID($gid);
            $jf_token = $u->getJotFormToken($gid);
            $j = new JotForm_Webhook();
            $j->setKey($jf_token);
            $g = new Google('', 'reauth', $_SESSION['token']);
            while ($p = $r->fetch_assoc()) {
                $token_id = $p['id'];
                $jf_id = $f->getDataFromID($p['form_id']);
                if ($j->doesFormExist($jf_id)) {
                    $jf_title = $j->getFormTitle($jf_id);
                }
                $temp_array = array();
                $temp_array[0] = $p['token'];
                $temp_array[1] = $jf_id;
                $temp_array[2] = $jf_title;
                $tk = $p['token'];
                $msql = "SELECT * from `tokens` WHERE `user_id` = '$this->uid' and `enabled` = '1' and `token` = '$tk'";
                $mr = DB::$c->query($msql);
                $sheets_list = array();
                while ($token_ids = $mr->fetch_assoc()) {
                    $token_id = $token_ids['id'];
                    $tsql = "SELECT DISTINCT `spreadsheet_id` from `hooks` WHERE `token` = '$token_id'";
                    $rt = DB::$c->query($tsql);
                    while ($pt = $rt->fetch_assoc()) {
                        //we have each spreadsheet now.
                        $sid = $pt['spreadsheet_id'];
                        $details = $g->getSpreadsheetDetailsFromID($s->getDataFromID($sid));
                        $ssql = "SELECT `sheet` from `hooks` WHERE `token` = '$token_id' and `spreadsheet_id` = '$sid'";
                        $rs = DB::$c->query($ssql);
                        $sheets_array = array();
                        while ($ps = $rs->fetch_assoc()) {
                            $shid = $ps['sheet'];
                            array_push($sheets_array, $details[1][$shid]);
                        }
                    }
                    array_push($sheets_list, [$details[0], $sheets_array]);
                }
                array_push($temp_array, $sheets_list);
                array_push($result, $temp_array);
            }
            return $result;
        } else {
            return [];
        }
    }

    public function addWebhooks($form_id, $spreadsheets, $append = false, $token = '')
    {
        $fc = new Forms();
        $sc = new Spreadsheets();
        $form_id = DB::$c->real_escape_string($form_id);
        $fid = $fc->saveID($form_id);
        if (!$append) {
            $hc = new PushId();
            $hook = $hc->generate();
            $isql = "INSERT into `tokens` VALUES (null, '$hook', '$this->uid', '$fid', '1');";
            DB::$c->query($isql);
            $hook_id = DB::$c->insert_id;
        } else {
            $ts = DB::$c->real_escape_string($token);
            $isql = "SELECT * from `tokens` WHERE `token` = '$ts';";
            $cr = DB::$c->query($isql);
            if ($cr->num_rows > 0) {
                $hook = $ts;
                $isql2 = "INSERT into `tokens` VALUES (null, '$ts', '$this->uid', '$fid', '1');";
                DB::$c->query($isql2);
                $hook_id = DB::$c->insert_id;
            } else {
                die('Fatal error');
            }
        }
        foreach ($spreadsheets as $s => $p) {
            // $s  -> spreadsheet id
            // $p  -> array of pages [indexes] => keys
            $st = DB::$c->real_escape_string($s);
            $sid = $sc->saveID($s);
            foreach ($p as $i => $pi) {
                $pif = DB::$c->real_escape_string($pi);
                $sql = "INSERT into `hooks` VALUES (null, '$hook_id', '$sid', '$pif');";
                DB::$c->query($sql);
            }
        }
        return $hook;
    }

    public function getWebhookID($token)
    {
        $token = DB::$c->real_escape_string($token);
        $sql = "SELECT * from `tokens` WHERE `token` = '$token' and `enabled` = '1' ORDER by `id` DESC";
        $r = DB::$c->query($sql);
        if ($r->num_rows > 0) {
            return $r->fetch_assoc()['id'];
        } else {
            return 0;
        }
    }

    public function getWebhookToken($id)
    {
        $token = DB::$c->real_escape_string($id);
        $sql = "SELECT * from `tokens` WHERE `id` = '$id' and `enabled` = '1'";
        $r = DB::$c->query($sql);
        if ($r->num_rows == 1) {
            return $r->fetch_assoc()['token'];
        } else {
            return 0;
        }
    }

    public function removeWebhook($token)
    {
        $success = false;
        while ($hook_id = $this->getWebhookID($token)) {
            $hook_id = DB::$c->real_escape_string($hook_id);
            $sql = "SELECT `id` from `tokens` WHERE `user_id` = '$this->uid' and `id` = '$hook_id'";
            $r = DB::$c->query($sql);
            if ($r->num_rows > 0) {
                $dsql3 = "DELETE FROM `fields` WHERE `token_id` = '$hook_id';";
                $d3 = DB::$c->query($dsql3);
                $dsql2 = "DELETE FROM `hooks` WHERE `token` = '$hook_id';";
                $d2 = DB::$c->query($dsql2);
                $dsql = "DELETE FROM `tokens` WHERE `user_id` = '$this->uid' and `id` = '$hook_id';";
                $d = DB::$c->query($dsql);
                $success = true;
            } else {
                continue;
            }
        }
        return $success;
    }

    public function getFormIDofWebhook($token)
    {
        $token = DB::$c->real_escape_string($token);
        $sql = "SELECT * from `tokens` WHERE `token` = '$token' and `enabled` = '1' ORDER by `id` DESC";
        $r = DB::$c->query($sql);
        if ($r->num_rows > 0) {
            $fid = $r->fetch_assoc()['form_id'];
            $fc = new Forms();
            return $fc->getDataFromID($fid);
        } else {
            return 0;
        }
    }

    public function disableWebhook($id)
    {
        $id = DB::$c->real_escape_string($id);
        $sql = "UPDATE `tokens` SET `enabled` = '0' WHERE `id` = '$id';";
        $r = DB::$c->query($sql);
    }

    public function enableWebhook($id)
    {
        $id = DB::$c->real_escape_string($id);
        $sql = "UPDATE `tokens` SET `enabled` = '1' WHERE `id` = '$id';";
        $r = DB::$c->query($sql);
    }

}
