# opencart-logstore_xapi

## ToDos

- Get hello world type extension working
- Create new DB table: oc_logstore_xapi_log
- Capture purchase event and add row to new DB table
- Create cron that turns log rows into batch of xapi statements with basic info to send to LRS
  - create transformer for the purchase event
- Add in all other needed info
- Do other events
