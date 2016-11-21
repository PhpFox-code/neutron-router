# phpfox-router
=====================================

### Add Simple Route

```php
return [
    'router.routes' => [
        'home'    => [
            'route'      => '/',
            'defaults' => [
                'controller' => Controller\IndexController::class,
                'action'     => 'index',
            ],
        ],
        'members'   => [
            'route'      => 'members',
            'defaults' => [
                'controller' => AdminIndexController::class,
                'action'     => 'index',
            ],

        ],
    ],
];
```

Implicit options using `(/<...>)`

```php
return [
    'router.routes' => [
        'event_action'    => [
            'route'      => '/blogs/browse(/<sort>)',
            'defaults' => [
                'controller' =>  Controller\IndexController::class,
                'action'     => 'browse',
                'sort'       => 'recents'
            ],
        ],
    ],
];
```


Add condition to pattern `wheres`

```php
return [
    'router.routes' => [
        'blog_action'    => [
            'route'      => '/blog/<id>/<action>',
            'wheres'=>[
                'id'=>'\d+',
                'action'=>(edit|delete|view|upgrade|settings)
            ],
            'defaults' => [
                'controller' => Controller\IndexController::class,
                'action'     => 'index',
            ],
        ],
    ],
];
```

### Create Filter

```php
return [
    'router.filters' => [
        '@profile' => [null, ProfileNameFilter::class],
    ],
];
```

Apply filter to route

__'filter'   => '@profile',__

```php
return[
    'router.routes'         => [
        'profile/members' => [
            'route'    => '<name>/{members}',
            'filter'   => '@profile',
            'defaults' => [
                'controller' => Controller\IndexController::class,
                'action'     => 'index',
            ],
        ],
    ],
];
```

You can add single one or multiple filter using [filter1, filter2,...], All filters will be traversed, The result 
will be true if the route passed all filter rules.

### Translate

Translated phrase must be apply before RoutingManager constructing.

>
> 1. Edit phrases in "$root/config/local.config.php"
> 2. Translated phrases must not contain blank or special characters.
> 3. Be careful that translated phrases does not duplicated another phrase in the same context.
> 

```php
return [
    'router.phrases' => [
        '{admincp}' => 'admincp',
        '{blog}'=>'bài-viết',
        '{members}=>'thành-viên',
    ],
];
```

### Get URL

```php
service('routing')->getUrl('profile/members', ['name'=>'codelego']);
// origin      "codelego/members"
// translated  "codelego/thành-viên" if {member} is configured to "thành-viên".  
```

### Environment Params

Example: 
```php
posts/en/policy
posts/vi/policy
```

With "en" or "vi" will be added by the current member locale, But you don't want to put $locale to every getUrl() invoked.
The simplest way is using EnvironmentParams.

