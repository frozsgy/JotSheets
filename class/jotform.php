<?php

require_once('jotform/JotForm.php');
require_once('curl.php');
require_once('integrations.php');
require_once('server.php');


class JotForm_Webhook
{
    private $jf;
    private $api_key;


    public function __construct($apikey = '')
    {
        $this->api_key = $apikey;
    }

    public function setKey($apikey)
    {
        $this->api_key = $apikey;
        return $this->isKeyValid();
    }

    public function isKeyValid()
    {
        try
        {
            $this->jf = new JotForm($this->api_key);
            return true;
        }
        catch (JotFormException $e)
        {
            return false;
        }
    }

    public function testWrite()
    {
        $test_json = array('questions' => [0 => ['type' => 'control_head',
                                                 'text' => 'Form Title',
                                                 'order' => '1',
                                                 'name' => 'Header',
                                                 ],
                                           1 =>  ['type' => 'control_textbox',
                                                  'text' => 'Text Box Title',
                                                  'order' => '2',
                                                  'name' => 'TextBox',
                                                  'validation' => 'None',
                                                  'required' => 'No',
                                                  'readonly' => 'No',
                                                  'size' => '20',
                                                  'labelAlign' => 'Auto',
                                                  'hint' => '',
                                                  ],
                                          ],
                           'properties' => ['title' => 'New Form',
                                            'height' => '600',
                                           ],
                           'emails' => [0 => ['type' => 'notification',
                                              'name' => 'notification',
                                              'from' => 'default',
                                              'to' => 'noreply@jotform.com',
                                              'subject' => 'New Submission',
                                              'html' => 'false',
                                             ],
                                       ]
        );
        try
        {
            $tr = $this->jf->createForm($test_json);
            $nid = $tr['id'];
            $this->jf->deleteForm($nid);
            return true;
        }
        catch (JotFormException $e)
        {
            return false;
        }
    }

    public function listForms($offset = 0, $limit = 0)
    {
        $conditions = array("status:ne" => "DELETED");
        $raw = $this->jf->getForms($offset, $limit, $conditions);
        $res = array();
        foreach ($raw as $i => $d) {
            $res[$d['id']] = $d['title'];
        }
        return $res;
    }

    public function doesFormExist($id)
    {
        try
        {
            $f = $this->jf->getForm($id);
            return true;
        }
        catch (JotFormException $e)
        {
            return false;
        }
    }

    /**
     * always use with doesFormExist method!
     */

    public function getFormTitle($id)
    {
        return $this->jf->getForm($id)['title'];
    }

    public function addWebhook($form_id, $hook)
    {
        $this->jf->createFormWebhook($form_id, $hook);
    }

    public function deleteWebhook($form_id, $hook_url)
    {
        $hooks = $this->jf->getFormWebhooks($form_id);
        if (in_array($hook_url, $hooks)) {
            $hid = array_search($hook_url, $hooks);
            $this->jf->deleteFormWebhook($form_id, $hid);
            return true;
        } else {
            return false;
        }
    }

    public function getQuestionLabels($id)
    {
        $q = $this->jf->getFormQuestions($id);
        $questions = array();
        $ignoretypes = array('control_pagebreak', 'control_button', 'control_head');
        foreach ($q as $m) {
            if (!in_array($m['type'], $ignoretypes)) {
                $questions[$m['qid']] = $m['text'];
            }
        }
        return $questions;
    }

}
