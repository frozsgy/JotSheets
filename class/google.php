<?php

require_once('google/vendor/autoload.php');
require_once('curl.php');
require_once('integrations.php');
require_once('server.php');
require_once('views/engine.php');

class Google {
    private $g;
    /**
     * Set the following variables according to your own Google Application;
     * $client_id, $client_secret, $name
     */
    private $client_id = 'CHANGEME';
    private $client_secret = 'CHANGEME';
    private $name = 'JotForm Webhook';
    private $redirect_uri;
    private $scope = 'https://www.googleapis.com/auth/userinfo.profile https://www.googleapis.com/auth/userinfo.email https://www.googleapis.com/auth/spreadsheets https://www.googleapis.com/auth/drive.metadata.readonly';
    private $token_check_uri = 'https://www.googleapis.com/oauth2/v3/tokeninfo';
    private $state;
    private $uid;
    private $code;
    private $gid;
    private $drive;
    private $sheets;
    private $cc;
    private $access_token;
    private $uu;
    private $refresh_token;
    private $view;

    public function __construct($uid = '', $code = '', $state = '') {
        $this->state = hash('sha512', 'jotform-webhook-client');
        $this->redirect_uri = ServerInfo::$root_url . 'login.php';
        $this->g = new Google_Client();
        $this->g->setClientId($this->client_id);
        $this->g->setClientSecret($this->client_secret);
        $this->g->setApplicationName($this->name);
        $this->g->setRedirectUri($this->redirect_uri);
        $this->g->addScope($this->scope);
        $this->g->setState($this->state);
        $this->g->setAccessType('offline');
        /**
         * The following line can uncommented if you would like to
         * force approval every time the user logs in.
         */
        //$this->g->setApprovalPrompt('force');
        if ($code == 'reauth') {
            $this->g->setAccessToken($state);
            $this->access_token = $state;
        } else {
            $this->code = $code;
        }
        if ($uid) {
            $this->uu = new User($uid);
            $this->uid = $uid;
            $this->gid = $this->uu->getGIDfromUID();
            $this->refresh_token = $this->uu->getRefreshToken($this->gid);
            $this->g->refreshToken($this->refresh_token);
            $re = $this->g->getAccessToken();
            $this->uu->updateUserToken($this->gid, $re['access_token']);
        }
        $this->cc = new oAuthcURL($this->token_check_uri);
        $this->view = new Views('empty');
    }

    public function redirectToAuth() {
        header('Location: ' . $this->g->createAuthUrl());
    }

    public function getAccessToken()
    {
        if ($this->code != 'reauth') {
            $this->g->authenticate($this->code);
            $this->access_token = $this->g->getAccessToken();
            $this->gid = $this->getGID();
            $ii = new User('', $this->gid, $this->access_token);
            return true;
        } else {
            return false;
        }
    }

    public function readAccessToken()
    {
        return $this->access_token;
    }

    private function getMe() {
        $oac = new Google_Service_Oauth2($this->g);
        return $oac->userinfo_v2_me->get();
    }

    public function getGID()
    {
        $me = $this->getMe();
        return $me['id'];
    }

    public function getName()
    {
        $me = $this->getMe();
        return $me['givenName'] . ' ' . $me['familyName'];
    }

    private function initializeSheets()
    {
        $this->sheets = new Google_Service_Sheets($this->g);
    }

    public function getSpreadsheetDetails($id)
    {
        $details = $this->getSpreadsheetDetailsFromID($id);
        $this->view->loadFile('google/subheader');
        $this->view->set('title', $details[0]);
        $res = $this->view->prepare();
        foreach ($details[1] as $k => $n) {
            $checked = '';
            if (sizeof($details[1]) == 1) {
                $checked = ' checked';
            }
            $this->view->loadFile('google/checkbox');
            $this->view->set('id', $id);
            $this->view->set('k', $k);
            $this->view->set('name', $n);
            $this->view->set('checked', $checked);
            $res .= $this->view->prepare();
        }
        return $res;
    }

    public function printSpreadsheetDetails($id)
    {
        echo $this->getSpreadsheetDetails($id);
    }

    public function printHiddenDetails($fields)
    {
        foreach ($fields as $details => $sheets) {
            foreach ($sheets as $k => $n) {
                $this->view->loadFile('google/hidden');
                $this->view->set('details', $details);
                $this->view->set('n', $n);
                $this->view->render();
            }
        }
    }

    public function getSpreadsheetNameFromID($id)
    {
        $this->initializeSheets();
        $r = $this->sheets->spreadsheets->get($id);
        return $r->{'properties'}->getTitle();
    }

    public function getSpreadsheetDetailsFromID($id)
    {
        $this->initializeSheets();
        $r = $this->sheets->spreadsheets->get($id);
        $title = $r->{'properties'}->getTitle();
        $sheets = array();
        foreach ($r->{'sheets'} as $m) {
            $sheets[$m->{'properties'}->getSheetId()] = $m->{'properties'}->getTitle();
        }
        $result = array();
        $result[0] = $title;
        $result[1] = $sheets;
        return $result;
    }

    private function initializeDrive()
    {
        $this->drive = new Google_Service_Drive($this->g);
    }

    public function listSpreadsheets($limit = 0, $page = 1)
    {
        $this->initializeDrive();
        //'pageSize' => 10,
        $optParams = array('fields' => "nextPageToken, files(id, name)",
                           'q' => "mimeType='application/vnd.google-apps.spreadsheet'"
        );
        $results = $this->drive->files->listFiles($optParams);
        $ids = array();
        foreach ($results['files'] as $j) {
            $ids[$j['id']] = $j['name'];
        }
        return $ids;
    }

    public function printSpreadsheetCheckboxes($s = []) {
        $ss = $this->listSpreadsheets();
        $sst = new Spreadsheets($this->uid);
        foreach ($ss as $k => $n) {
            $checked = '';
            if (in_array($k, $s)) {
                $sst->saveID($k);
                $checked = ' checked';
            }
            $this->view->loadFile('google/checkbox_ss');
            $this->view->set('k', $k);
            $this->view->set('n', $n);
            $this->view->set('checked', $checked);
            $this->view->render();
        }
    }

    public function getSpreadsheetSelect($s = [])
    {
        $ss = $this->listSpreadsheets();
        $sst = new Spreadsheets($this->uid);
        $options = '';
        foreach ($ss as $k => $n) {
            $this->view->loadFile('google/option');
            $this->view->set('k', $k);
            $this->view->set('n', $n);
            $options .= $this->view->prepare();
        }
        $this->view->loadFile('google/select');
        $this->view->set('options', $options);
        return $this->view->prepare();
    }

    public function printSpreadsheetSelect($ss = [])
    {
        echo $this->getSpreadsheetSelect($ss);
    }

    public function isAccessTokenValid()
    {
        $getArray = array('access_token' => $this->access_token);
        $this->cc->setGetFields($getArray);
        $rv = $this->cc->process();
        $jr = json_decode($rv);
        return (bool)$jr->{'expires_in'};
    }


    public function appendToSpreadsheet($sid, $sheet, $data)
    {
        $this->initializeSheets();
        $body = new Google_Service_Sheets_ValueRange(['values' => [$data]]);
        $params = ['valueInputOption' => 'RAW'];
        $details = $this->getSpreadsheetDetailsFromID($sid);
        $range = "'" . $details[1][$sheet] . "'!A1";
        $result = $this->sheets->spreadsheets_values->append($sid, $range, $body, $params);
    }

    public function createSpreadsheet($name)
    {
        $this->initializeSheets();
        $spreadsheet = new Google_Service_Sheets_Spreadsheet(['properties' => ['title' => $name]]);
        $spreadsheet = $this->sheets->spreadsheets->create($spreadsheet, ['fields' => 'spreadsheetId']);
        return $spreadsheet->spreadsheetId;
    }

    public function freezeRow($sid, $sheet, $columnCount)
    {
        $this->initializeSheets();
        $requestBody = new Google_Service_Sheets_BatchUpdateSpreadsheetRequest();
        $requestBody->setRequests(
            ['updateSheetProperties' => [
                'properties' => [
                    'sheetId' => $sheet,
                    'gridProperties' => [
                        'frozenRowCount' => 1,
                        'rowCount' => 1000,
                        'columnCount' => $columnCount
                    ],
                ],
                'fields' => 'gridProperties'
                ],
            ]
        );
        $response = $this->sheets->spreadsheets->batchUpdate($sid, $requestBody);
    }

}
