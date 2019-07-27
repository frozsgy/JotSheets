<?php
session_start();
require_once('./class/google.php');
require_once('./class/hook.php');
require_once('./class/user.php');
require_once('./class/jotform.php');
require_once('./views/engine.php');

$view = new Views('main/header');
$view->set('title', 'hello again');
$view->render();
$view->loadAndRender('main/body');

if (isset($_SESSION['token'])) {
    if($_SESSION['lt'] + 3600 < time()) {
        session_destroy();
        session_start();
        header('Location: index.php');
    }
    $g = new Google('', 'reauth', $_SESSION['token']);
    $gid = $g->getGID();
    $u = new User();
    $uid = $u->getUIDfromGID($gid);
    $view->loadFile('user/welcome');
    $view->set('name', $g->getName());
    $view->render();
    $jf_token = $u->getJotFormToken($gid);
    $j = new JotForm_Webhook();
    if ($jf_token == null  || !$j->setKey($jf_token) || !($j->testWrite())) {
        if (isset($_POST['jf_api'])) {
            $jf_api = $_POST['jf_api'];
            $u->setJotFormToken($gid, $jf_api);
            $j->setKey($jf_api);
            if (!$j->isKeyValid() || !$j->testWrite()) {
                $view->loadAndRender('jotform/token_invalid');
            } else {
                header('Location: index.php');
            }
        }
        $view->loadAndRender('jotform/connect');
    } else {
        $h = new Hook($uid);
        $_SESSION['jf'] = true;
        $view->loadAndRender('main/webhooks');
        $h->printWebhooks();
    }
} else {
    $view->loadAndRender('user/login');
}
$view->loadAndRender('main/footer');
