<?php
require('vendor/autoload.php');

use NoahBuscher\Macaw\Macaw;

Macaw::get('/api/method-get', 'application\controllers\apiController@method_get');
Macaw::post('/api/method-post', 'application\controllers\apiController@method_post');

Macaw::dispatch();
?>
