# PHP-FPM tuner

Simple script that helps tuning PHP-FPM.

```
❯ php php-fpm-tuner.php

pm.max_children = 11
start_servers = 3
min_spare_servers = 3
max_spare_servers = 8
```

The script calculates `pm.max_children` based on available free memory and memory used for each worker (it is either determined from currently running workers or php.ini memory limit). `start_servers`, `min_spare_servers`, and `max_spare_servers` are taking both memory limits and CPU cores available into account.
