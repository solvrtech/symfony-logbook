# symfony-logbook

Installitation
```bash
composer require solvrtech/symfony-logbook
```

Configuration
```bash
/config/packages/monolog.yaml

monolog:
  handlers:
    logbook:
      type: stream
      levele: debug
```

```bash
/config/packages/logbook.yaml

logbook:
  api:
    # The base url of logbook app.
    url: "https://logbook.solvrtech.id"
    
    # The key of logbook client app.
    key: "4eaa39a6ff57c4d5b2cd0a01297e219e323380ea43ef2565b4774d710f727dd243a48aa9ae32f10757d19246f5167e945d4d521b2dbc0f5119bbb1c2b493ef70"
```


```bash
/config/services.yaml

parameters:
  version: "1.0.0"
```
