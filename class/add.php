<?php

require_once('class/google.php');
require_once('class/hook.php');
require_once('class/server.php');
require_once('class/jotform.php');
require_once('class/user.php');
require_once('class/field.php');
require_once('class/unique.php');
require_once('views/engine.php');

abstract class AddBase
{
    protected $google;
    protected $user;
    protected $jotform;
    protected $dest;
    protected $token;
    protected $view;

    public function __construct($google, $user, $jotform)
    {
        $this->google = $google;
        $this->user = $user;
        $this->jotform = $jotform;
        $this->view = new Views('empty', $this->dest, $this->token);
    }

    protected function getColumnNames($m)
    {
        $n = $m - 1;
        $r = "";
        while ($n >= 0) {
            $r = chr($n%26 + 0x41) . $r;
            $n = intval($n / 26) - 1;
        }
        return $r;
    }

    protected function isAppend()
    {
        return (bool)($this->dest == 'append.php');
    }

    protected function isAdd()
    {
        return (bool)($this->dest == 'add.php');
    }

    public function end()
    {
        $this->view->loadAndRender('add/end');
    }

    public function step1()
    {
        if ($this->isAdd()) {
            $formfields = '';
            $forms = $this->jotform->listForms(0, 25);
            foreach ($forms as $i => $v) {
                $this->view->loadFile('add/option');
                $this->view->set('val', $i);
                $this->view->set('name', $v);
                $formfields .= $this->view->prepare();
            }
            $this->view->loadFile('add/step1_action_add');
            $this->view->set('forms', $formfields);
        } else {
            $this->view->loadFile('add/step1_action_append');
        }
        $this->view->render();
        $this->view->loadAndRender('add/step1_footer');
    }

    public function step2_1($form_id = '')
    {
        $this->view->loadFile('add/step2_header');
        $this->view->render();
        if ($this->isAdd()) {
            $this->view->loadFile('add/step2_action_add');
            $this->view->set('form_id', $form_id);
            $this->view->render();
        } else {
            $this->view->loadAndRender('add/step2_action_append');
        }
        $this->view->loadAndRender('add/step2_footer_new');
    }

    public function step2_2($form_id = '')
    {
        $this->view->loadFile('add/step2_header');
        $this->view->render();
        if ($this->isAdd()) {
            $this->view->loadFile('add/step2_action_add');
            $this->view->set('form_id', $form_id);
            $this->view->render();
        } else {
            $this->view->loadAndRender('add/step2_action_append');
        }
        $this->view->loadFile('add/step2_footer_select');
        $this->view->set('ss_select', $this->google->getSpreadsheetSelect());
        $this->view->render();
    }

    public function step3_1($form_id = '', $title, $uid)
    {
        $sid = $this->google->createSpreadsheet($title);
        $ss = [$sid => [0 => 0]];
        $h = new Hook($uid);
        if ($this->isAppend()) {
            $hook_id = $this->token;
        } else {
            $hook_id = $h->addWebhooks($form_id, $ss);
        }
        $hook_link = ServerInfo::$root_url . 'hook/' . $hook_id;
        if ($this->isAppend()) {
            $form_id = $h->getFormIDofWebhook($hook_id);
            $h->addWebhooks($form_id, $ss, true, $this->token);
            $_SESSION['appendSheet'] = [$sid => [0]];
        } else {
            $this->jotform->addWebhook($form_id, $hook_link);
        }
        return $h->getWebhookID($hook_id);
    }

    public function step3_2($form_id = '', $spreadsheets)
    {
        $this->view->loadFile('add/step3_header');
        $this->view->render();
        if ($this->isAppend()) {
            $this->view->loadFile('add/step3_action_append');
        } else {
            $this->view->loadFile('add/step3_action_add');
            $this->view->set('form_id', $form_id);
        }
        $this->view->render();
        $this->view->loadFile('add/step3_footer');
        $details = '';
        foreach ($spreadsheets as $k) {
            $details .= $this->google->getSpreadsheetDetails($k);
        }
        $this->view->set('ss_details', $details);
        $this->view->render();
    }

    public function step4($form_id = '', $sheets, $uid)
    {
        $h = new Hook($uid);
        if ($this->isAppend()) {
            $hook_id = $this->token;
            $form_id = $h->getFormIDofWebhook($hook_id);
            $h->addWebhooks($form_id, $sheets, true, $this->token);
            $_SESSION['appendSheet'] = $sheets;
        } else {
            $hook_id = $h->addWebhooks($form_id, $sheets);
            $hook_link = ServerInfo::$root_url . 'hook/' . $hook_id;
            $this->jotform->addWebhook($form_id, $hook_link);
        }
        return $h->getWebhookID($hook_id);
    }

    public function step9($form_id = '', $uid = '')
    {
        if ($this->isAppend()) {
            $h = new Hook($uid);
            $hook_id = $this->token;
            $form_id = $h->getFormIDofWebhook($hook_id);
        }
        $questions = $this->jotform->getQuestionLabels($form_id);
        $count = count($questions);
        $colsec = '';
        $columns = '';
        if ($this->isAdd()) {
            for ($i=1; $i <= $count; $i++) {
                $this->view->loadFile('add/columns');
                $this->view->set('id', $i);
                $this->view->set('columnNames', $this->getColumnNames($i));
                $columns .= $this->view->prepare();
            }
        } else {
            $colsec = ' colsec';
        }
        $questions_view = '';
        foreach ($questions as $id => $v) {
            $this->view->loadFile('add/questions');
            $this->view->set('id', $id);
            $this->view->set('colsec', $colsec);
            $this->view->set('name', $v);
            $questions_view .= $this->view->prepare();
        }
        $this->view->loadAndRender('add/step9_header');
        if ($this->isAdd()) {
            $this->view->loadFile('add/step9_action_add');
        } else {
            $this->view->loadFile('add/step9_action_append');
        }
        $this->view->set('columns', $columns);
        $this->view->set('questions', $questions_view);
        $this->view->render();
    }

    protected function getFormFields($data, $hook_id)
    {
        $arr = json_decode($data, true);
        $f = new Field($hook_id);
        $dd = $arr[0];
        $formFields = array();
        foreach ($dd['items'] as $v) {
            preg_match('/id="([0-9]*)"/', $v['html'], $id);
            $qid = $id[1];
            $column = $v['index'];
            $fid = $f->addField($column, $qid);
            $formFields[$column] = $qid;
        }
        ksort($formFields);
        return $formFields;
    }

    protected function getFieldNames($hook_id, $fields)
    {
        $unique = new Unique();
        $field_names = array();
        $form_id = $unique->getFIDbyID($hook_id);
        $forms = new Forms();
        $form_id = $forms->getDataFromID($form_id);
        $questions = $this->jotform->getQuestionLabels($form_id);
        foreach ($fields as $ff) {
            array_push($field_names, $questions[$ff]);
        }
        array_push($field_names, "Slug", "Event ID");
        return $field_names;
    }

    protected function processSheets($hook_id, $field_names)
    {
        $unique = new Unique();
        if ($this->isAdd()) {
            $sheets = $unique->getSpreadsheetsbyID($hook_id);
        } else {
            $sheets = $_SESSION['appendSheet'];
        }
        $sc = new Spreadsheets();
        foreach ($sheets as $ss => $s) {
            if ($this->isAppend()) {
                $ss = $sc->getID($ss);
            }
            $ss = $sc->getDataFromID($ss);
            foreach ($s as $pages) {
                $this->google->appendToSpreadsheet($ss, $pages, $field_names);
                $this->google->freezeRow($ss, $pages, count($field_names));
            }
        }
    }

    public function step8($data, $hook_id)
    {
        $formFields = $this->getFormFields($data, $hook_id);
        $field_names = $this->getFieldNames($hook_id, $formFields);
        if (($this->isAppend() && isset($_SESSION['appendSheet'])) || $this->isAdd()) {
            $this->processSheets($hook_id, $field_names);
        } else {
            $this->view->loadAndRender('error/fatal');
        }
    }
}
