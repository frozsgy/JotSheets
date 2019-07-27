<?php
require_once('./class/google.php');
require_once('./views/engine.php');
$view = new Views('main/header');
if (isset($_SESSION['token'])) {
    $view->set('title', 'login error');
    $view->render();
    $view->loadAndRender('main/body');
    $view->loadAndRender('error/already');
    $view->loadAndRender('main/footer');
    exit;
} elseif (isset($_GET['code']) && isset($_GET['state'])) {
    $rc = $_GET['code'];
    $rs = $_GET['state'];
    $g = new Google('', $rc, $rs);
    $ttk = $g->getAccessToken();
    $ttk = $g->readAccessToken();
    if (!session_id()) {
        session_start();
    }
    $_SESSION['token'] = $ttk;
    $_SESSION['lt'] = time();
    $view->set('title', 'login successful');
    $view->render();
    $view->loadAndRender('main/body');
    $view->loadFile('user/login_redirect');
    $view->set('name', $g->getName());
    $view->render();
    $view->loadAndRender('main/footer');

} else {
    $g = new Google();
    $g->redirectToAuth();
    exit();
}
