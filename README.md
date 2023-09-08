# symfony-logbook

Installitation

```bash
composer require solvrtech/symfony-logbook
```

Configuration<br>
Enable the bundle by adding it to the list of registered bundles
in the `config/bundles.php` file

```bash
// config/bundles.php

return [
    // ...
    Solvrtech\Logbook\LogbookBundle::class => ['all' => true],
]
```

```bash
// config/packages/logbook.yaml

logbook:
    api:
        # The base url of the LogBook installation.
        url: "https://logbook.com"
        # The generated API key that LogBook assigned for the Symfony app.
        key: "4eaa39a6ff57c4d5b2cd0a..."
        
    # Instance ID is a unique identifier per instance of your apps.
    instance_id: "default"
    
    # This configuration defines a logbook transport that can handle logs either synchronously or asynchronously.   
    transport: 'sync://'
    
    # Asynchronous Transport:
    # - To use Doctrine for Asynchronous log handling:
    #   transport: 'doctrine://default?batch=15'
    #
    # - To use Redis for Asynchronous log handling:
    #   transport: 'redis://localhost:6379/logs?batch=15'
```

When you use asynchronous transport to handle the logs, you will need to consume them. You can do this with the
``logbook:log:consume`` command:

```bash
php bin/console logbook:log:consume
```

```bash
// config/routes/logbook.yaml

logbook_health:
    resource: "@LogbookBundle/Resources/config/routes.yaml"
    prefix: /logbook-health
```

```bash
// config/packages/security.yaml

security:
    providers:
        // ...
        logbook_provider:
            id: Solvrtech\Logbook\Security\LogbookUserProvider

    firewalls:
        health_check:
            pattern: ^/logbook-health
            stateless: true
            provider: logbook_provider
            custom_authenticator: logbook.authenticator

```

```bash
// config/packages/monolog.yaml

monolog:
    handlers:
        // ...
        logbook:
            type: stream
            level: debug
```

```bash
// config/services.yaml

parameters:
    // ...
    version: "1.0.0"
```
