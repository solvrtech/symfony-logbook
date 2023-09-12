# symfony-logbook

### Installitation

To install the `solvrtech/symfony-logbook` bundle, use Composer:

```bash
composer require solvrtech/symfony-logbook
```

## Configuration

### Enable the Bundle

To enable the bundle, add it to the list of registered bundles in the `config/bundles.php` file:

```bash
// config/bundles.php

return [
    // ...
    Solvrtech\Logbook\LogbookBundle::class => ['all' => true],
]
```

### Configure LogBook

Create a configuration file `config/packages/logbook.yaml` to configure LogBook:

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

### Consume Logs (Asynchronous Transport)

When you use asynchronous transport to handle the logs, you need to consume them. You can do this with the
``logbook:log:consume`` command:

```bash
php bin/console logbook:log:consume
```

### Configure LogBook Routes

Add LogBook health check routes to your Symfony application by including them in `config/routes/logbook.yaml`:

```bash
// config/routes/logbook.yaml

logbook_health:
    resource: "@LogbookBundle/Resources/config/routes.yaml"
    prefix: /logbook-health
```

### Configure Security

Update your `config/packages/security.yaml` file to configure security settings related to LogBook:

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

### Configure Monolog Handler

To use the LogBook handler with Monolog, update your `config/packages/monolog.yaml` file:

```bash
// config/packages/monolog.yaml

monolog:
    handlers:
        // ...
        logbook:
            type: stream
            level: debug
```

### Configure App Version

```bash
// config/services.yaml

parameters:
    // ...
    version: "1.0.0"
```
