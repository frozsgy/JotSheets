# JotSheets

JotSheets allows you to create Webhook integrations that connects your JotForms to Google Spreadsheets. With JotSheets, you can connect a form to multiple sheets, with various different form field mappings with only a couple of mouse clicks.

JotSheets was developed as an internship project at JotForm.

[![Source Code](http://img.shields.io/badge/source-frozsgy/jotsheets-blue.svg?style=flat-square)](https://github.com/frozsgy/JotSheets)
[![Latest Version](https://img.shields.io/github/release/frozsgy/jotsheets.svg?style=flat-square)](https://github.com/frozsgy/JotSheets/releases)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](https://github.com/frozsgy/JotSheets/blob/master/LICENSE)

## Requirements ##
* [PHP 7 or higher](http://www.php.net/)
* [mySQL](https://www.mysql.com/) or [MariaDB](https://mariadb.org/)
* [Google APIs Client Library for PHP](https://github.com/googleapis/google-api-php-client)
* [JotForm API Client for PHP](https://github.com/jotform/jotform-api-php)
* Apache server with RewriteEngine on

### Used Libraries ###

* This project uses [HTML5Sortable](https://github.com/lukasoppermann/html5sortable) JavaScript library for drag & drop sort buttons.

## Installation ##

After **downloading the release**, you need to do the following changes in order to make the script work.
* Create a database and import `./db.sql`
* Fill in the necessary database details inside `./class/db.php`
* Update the server information in `./class/server.php`
* Fill in the API keys in `./class/google.php`
* Install Google APIs Client Library for PHP under `./class/google/`.
* Install JotForm API Client for PHP under `./class/jotform/`.

### Notes ###

* The Google integrations class will look for the autoloader at the following location: `./class/google/vendor/autoload.php`
* The JotForm integrations class will look for the JotForm API Client at the following location: `./class/jotform/JotForm.php`
* Please make sure that both classes can read the library files properly.

## Live Demo ##

Available at http://jotform.ozanalpay.com

## Comments, Pull Requests, Bugs ##

Please create an issue, or directly submit a pull request. They are always appreciated.
