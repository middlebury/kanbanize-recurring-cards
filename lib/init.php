<?php

require_once(dirname(__FILE__)."/autoload.php");
require_once(dirname(__FILE__)."/../vendor/autoload.php");
require_once(dirname(__FILE__)."/../config.php");

if (empty($config['KANBANIZE']['API_KEY'])) {
  throw new Exception("\$config['KANBANIZE']['API_KEY'] must be defined in config.php");
}
if (empty($config['KANBANIZE']['SUBDOMAIN'])) {
  throw new Exception("\$config['KANBANIZE']['SUBDOMAIN'] must be defined in config.php");
}
$kanbanize = EtuDev_KanbanizePHP_API::getInstance();
$kanbanize->setSubdomain($config['KANBANIZE']['SUBDOMAIN']);
$kanbanize->setApiKey($config['KANBANIZE']['API_KEY']);

if (empty($config['TIMEZONE'])) {
  date_default_timezone_set('UTC');
}
