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
