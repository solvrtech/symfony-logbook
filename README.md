# symfony-logbook

Intallitation
```bash
composer require solvrtech/symfony-logbook
```

Configuration
```bash
/config/packages/logbook.yaml

logbook:
  api:
    # The base url of logbook app.
    url: "%env(LOGBOOK_API_URL)%"
    
    # The key of logbook client app.
    key: "%env(LOGBOOK_API_KEY)%"

  # The minimum log level allowed to be stored.
  # DEBUG
  # INFO
  # NOTICE
  # WARNING
  # ERROR
  # CRITICAL
  # ALERT
  # EMERGENCY
  level: "%env(LOGBOOK_LEVEL)%"
```

```bash
.env

###> solvrtech/symfony-logbook ###
LOGBOOK_API_URL="https://logbook.solvrtech.id"
LOGBOOK_API_KEY="4eaa39a6ff57c4d5b2cd0a01297e219e323380ea43ef2565b4774d710f727dd243a48aa9ae32f10757d19246f5167e945d4d521b2dbc0f5119bbb1c2b493ef70"
LOGBOOK_LEVEL=DEBUG
###< solvrtech/symfony-logbook ###
```

```bash
/config/services.yaml

parameters:
  version: "1.0.0"
```
