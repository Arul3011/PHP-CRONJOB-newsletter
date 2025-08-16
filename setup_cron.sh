

#!/bin/bash


PHP_FILE="$(pwd)/cron.php"
LOG_FILE="$(pwd)/cron.log"


CRON_JOB="* * * * * php $PHP_FILE >> $LOG_FILE 2>&1"



if crontab -l 2>/dev/null | grep -F "$PHP_FILE" >/dev/null; then
    echo " Cron job already exists for: $PHP_FILE"
    exit 0
fi

(crontab -l 2>/dev/null; echo "$CRON_JOB") | crontab -
echo "Cron job added to run every minute: $PHP_FILE"
