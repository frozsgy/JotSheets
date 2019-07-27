<?php

class ServerInfo
{
    public static $root_url;
}

/**
 * Update the following line to fit your exact URL.
 * For example, if the portal runs at http://webhooks.jotform.com/
 * the following line should suffice.
 * ServerInfo::$root_url = 'http://webhooks.jotform.com/';
 */

ServerInfo::$root_url = 'http://' . $_SERVER['SERVER_NAME'] . '/jotform-webhook/';
