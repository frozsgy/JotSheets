<?php
require_once('./views/engine.php');
$view = new Views('main/header');
$view->set('title', 'logout successful');
$view->render();
$view->loadAndRender('main/body');
if (!session_id()) {
    session_start();
}
if (isset($_SESSION['token'])) {
    $view->loadAndRender('user/logout');
    session_destroy();
} else {
    $view->loadAndRender('error/login');
}
$view->loadAndRender('main/footer');
