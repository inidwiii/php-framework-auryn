<?php

use App\Providers\AppServiceProvider;
use Auryn\Core\Env;

return [
  'singletons' => [
    'env' => Env::class
  ],
  'providers' => [
    AppServiceProvider::class,
  ],
];