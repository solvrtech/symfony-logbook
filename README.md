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
  Solvrtech\Symfony\Logbook\LogbookBundle::class => ['all' => true],
]
```

```bash
// config/packages/logbook.yaml

logbook:
  api:
    # The base url of logbook app.
    url: "https://logbook.com"
    
    # The API key of logbook client app.
    key: "4eaa39a6ff57c4d5b2cd0a..."
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
