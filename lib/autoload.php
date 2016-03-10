<?php

spl_autoload_register(function ($class) {
  include dirname(__FILE__).'/' . str_replace('\\', '/', str_replace('_', '/', $class)) . '.php';
});
