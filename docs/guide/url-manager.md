Multilingual URL Manager
============

Multilingual URL Manager helps you to achieve user friendly and search engine 
friendly URLs for your multilingual site. You can use language switcher to switch 
between languages.


Configuration
------

1. To use UrlManager, you have to update `urlManager` component configuration 
in your application configuration. This is an example of configuration:

```php
<?php

    'components' => [
        'urlManager' => [
            'class' => 'h0rseduck\multilingual\components\UrlManager',
        ],
    ]
```

Application with this configuration will generate user friendly URLs. Example:

- mysite.com/en/contacts
- mysite.com/es/contacts
- mysite.com/en/user/update

2. The following code shows how can we render `LanguageSwitcher` widget:

```php
<?php

    use h0rseduck\multilingual\widgets\LanguageSwitcher;

    echo LanguageSwitcher::widget();
```