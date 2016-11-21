<?php

namespace Phpfox\Router;

return [
    'router.filters' => [
        '@profile' => [null, ProfileNameFilter::class],
    ],
    'routes'         => [],
    'services'       => [
        'routing'        => [null, RouteManager::class,],
        'router.filters' => [null, FilterContainer::class],
    ],
];