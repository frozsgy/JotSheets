<?php
session_start();
require_once('class/addhook.php');
$view = new Views('main/header');
$view->set('title', 'add a new webhook');
$view->render();
$view->loadFile('add/body');
$view->set('header_title', 'Add a New Webhook');
$view->render();
if (isset($_SESSION['token'])) {
    if($_SESSION['lt'] + 3600 < time()) {
        session_destroy();
        session_start();
        $view->loadAndRender('error/login');
    }
    $g = new Google('', 'reauth', $_SESSION['token']);
    if (!isset($_SESSION['jf']) || (isset($_SESSION['jf']) && $_SESSION['jf'] !== true)) {
        $view->loadAndRender('jotform/auth_fail');
        $view->loadAndRender('main/footer');
        die();
    } else {
        $gid = $g->getGID();
        $u = new User();
        $jf_token = $u->getJotFormToken($gid);
        $j = new JotForm_Webhook();
        $j->setKey($jf_token);
        $p = @$_GET['p'];
        $h = new AddHook($g, $u, $j);
        $uid = $u->getUIDfromGID($gid);
        $hook = new Hook($uid);
        switch ($p)
        {
            case 2 :
                if (isset($_POST['form_url'])) {
                    $form_id = preg_replace('/https:\/\/form.jotform.com\/([0-9]*)/', '$1', $_POST['form_url']);
                }
                if (!isset($_POST['status'])) {
                    $h->step1();
                } elseif ($_POST['status'] == 1) {
                    $h->step2_1($form_id);
                } else {
                    $h->step2_2($form_id);
                }
            break;
            case 3 :
                if (isset($_POST['form_id']) && isset($_POST['ss'])) {
                    $ss = $_POST['ss'];
                    $h->step3_2($_POST['form_id'], $_POST['ss']);
                } elseif (isset($_POST['form_id']) && isset($_POST['title'])) {
                    if ($hook_id = $h->step3_1($_POST['form_id'], $_POST['title'], $uid)) {
                        $hook->disableWebhook($hook_id);
                        $h->step9($_POST['form_id']);
                        $_SESSION['hid'] = $hook_id;
                    } else {
                        $view->loadAndRender('error/webhook');
                    }
                } else {
                    $h->step1();
                }
            break;
            case 4 :
                if (isset($_POST['form_id']) && isset($_POST['sp'])) {
                    if ($hook_id = $h->step4($_POST['form_id'], $_POST['sp'], $uid)) {
                        $hook->disableWebhook($hook_id);
                        $_SESSION['hid'] = $hook_id;
                        $h->step9($_POST['form_id']);
                    } else {
                        $view->loadAndRender('error/webhook');
                    }
                } else {
                    $h->step1();
                }
            break;
            case 9 :
                if (isset($_GET['form_id'])) {
                    $form_id = $_GET['form_id'];
                    $h->step9($form_id);
                } else {
                    $h->step1();
                }
            break;
            case 8 :
                if (isset($_POST['data'])) {
                    $data = $_POST['data'];
                    $unique = new Unique();
                    if ($unique->getUIDbyID($_SESSION['hid']) == $uid) {
                        $h->step8($data, $_SESSION['hid']);
                    } else {
                        header('Location: add.php');
                    }
                } else {
                    $h->step1();
                }
            break;
            case 6 :
                $hook->enableWebhook($_SESSION['hid']);
                unset($_SESSION['hid']);
                $h->end();
            break;
            default :
                $h->step1();
        }
    }
} else {
    $view->loadAndRender('error/login');
}
$view->loadAndRender('main/footer');
if (!isset($_SESSION['token'])) exit;
