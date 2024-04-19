<?php 

require 'vendor/autoload.php';

use Yasmin\Route;
Route::get('/', function () {
    return response('Hello World !');
});

Yasmin\Framework::run();