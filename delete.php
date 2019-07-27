<?php
session_start();
require_once('class/google.php');
require_once('class/hook.php');
require_once('class/server.php');
require_once('class/jotform.php');
require_once('views/engine.php');
$view = new Views('main/header');
$view->set('title', 'delete a webhook');
$view->render();
$view->loadFile('body_add');
$view->set('header_title', 'Delete a Webhook');
$view->render();
if (isset($_SESSION['token']) && isset($_GET['id'])) {
    $g = new Google('', 'reauth', $_SESSION['token']);
    $id = $_GET['id'];
    $gid = $g->getGID();
    $u = new User();
    $uid = $u->getUIDfromGID($gid);
    $h = new Hook($uid);
    $jf_token = $u->getJotFormToken($gid);
    $j = new JotForm_Webhook();
    $j->setKey($jf_token);
    $hook_url = ServerInfo::$root_url . 'hook/' . $id;
    $form_id = $h->getFormIDofWebhook($id);
    if ($j->deleteWebhook($form_id, $hook_url) && $h->removeWebhook($id) ) {
        $view->loadAndRender('add/delete');
    } else {
        $view->loadAndRender('error/wrong');
    }

} else {
    $view->loadAndRender('error/login');
}
$view->loadAndRender('main/footer');
if (!isset($_SESSION['token'])) exit;
