<?php

class oAuthcURL
{
    private $url;
    private $postfields;
    private $getfields;
    private $headers;
    private $cc;
    private $rv;

    public function __construct($url = '')
    {
        $this->cc = curl_init();
        $this->url = $url;
    }

    public function __destruct()
    {
        curl_close($this->cc);
    }

    public function clearParameters()
    {
        curl_reset($this->cc);
        $this->postfields = '';
        $this->headers = '';
        $this->rv = '';
    }

    public function setPostFields($postfields)
    {
        $this->postfields = http_build_query($postfields);
    }

    public function setGetFields($getfields)
    {
        $this->getfields = http_build_query($getfields);
    }

    public function setHeaders($headers)
    {
        $this->headers = $headers;
    }

    public function setURL($url)
    {
        $this->url = $url;
    }

    public function process() {
        if (!empty($this->getfields)) {
            curl_setopt($this->cc, CURLOPT_URL, $this->url . '?' . $this->getfields);
        } else {
            curl_setopt($this->cc, CURLOPT_URL, $this->url);
        }
        if (!empty($this->headers)) {
            curl_setopt($this->cc, CURLOPT_HTTPHEADER, $this->headers);
        }
        if (!empty($this->postfields)) {
            curl_setopt($this->cc, CURLOPT_POST, 1);
            curl_setopt($this->cc, CURLOPT_POSTFIELDS, $this->postfields);
        }
        curl_setopt($this->cc, CURLOPT_RETURNTRANSFER, true);
        $this->rv = curl_exec($this->cc);
        return $this->rv;
    }

}
