# opencart-logstore_xapi

## ToDos

- Get hello world type extension working
- Create new DB table: oc_logstore_xapi_log
- Capture purchase event and add row to new DB table
- Create cron that turns log rows into batch of xapi statements with basic info to send to LRS
  - create transformer for the purchase event
- Add in all other needed info
- Do other events

## Usage

1) Install `php-cgi` (`sudo apt-get install php5-cgi`)
2) Set up a cron to run `sudo php-cgi -f /var/www/html/opencart2.3/upload/index.php route=extension/module/logstore_xapi` every 5 minutes.