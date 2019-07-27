<?php

require_once('class/integrations.php');
require_once('class/google.php');
require_once('class/field.php');

if (isset($_GET['id']) && isset($_POST['rawRequest'])) {
    $unique = new Unique();
    $uniqueID = $_GET['id'];
    $uids = $unique->getUniqueIDs($uniqueID);
    foreach ($uids as $i) {
        $uid = $unique->getUID($uniqueID);
        $g = new Google($uid);
        $ss = new Spreadsheets();
        $f = new Field($i);
        $forms = $unique->getFID($uniqueID);
        $result = $_POST['rawRequest'];
        $id = $_POST['submissionID'];
        $obj = json_decode($result, true);
        $slug = $obj['slug'];
        $event_id = $obj['event_id'];
        $spreadsheets = $unique->getSpreadsheetsbyID($i);
        $pushy = array();
        $fields = $f->getFields();
        $formData = array();
        foreach ($obj as $k => $v) {
            if ($k == 'slug' || $k == 'event_id') continue;
            preg_match('/q([0-9]+)_(.*?)/s', $k, $l);
            if (is_array($v)) {
                $v = implode(', ', $v);
            }
            $formData[$l[1]] = $v;
        }
        foreach ($fields as $k => $v) {
            $pushy[$k] = $formData[$v];
        }
        array_push($pushy, $slug);
        array_push($pushy, $event_id);
        foreach ($spreadsheets as $sid => $sheets) {
            $ssid = $ss->getDataFromID($sid);
            foreach ($sheets as $shid) {
                $g->appendToSpreadsheet($ssid, $shid, $pushy);
            }
        }
    }
} else {
    die('missing parameters');
}
