<?php

namespace Phpfox\Router;

return [
    'router.filters' => [
        '@profile' => [null, ProfileNameFilter::class],
    ],
    'router.phrases' => [],
    'router.routes'         => [],
    'services'       => [
        'router'        => [null, RouteManager::class,],
        'router.filters' => [null, FilterContainer::class],
    ],
];