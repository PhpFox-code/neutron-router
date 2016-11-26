<?php

namespace Phpfox\Router;

return [
    'router.filters' => [
        '@profile' => [null, ProfileNameFilter::class],
    ],
    'router.phrases' => [],
    'router.routes'         => [],
    'services'       => [
        'router'        => [null, RouteContainer::class,],
        'router.filters' => [null, FilterContainer::class],
    ],
];