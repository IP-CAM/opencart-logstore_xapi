# opencart-logstore_xapi

Built in similar fashion to [moodle-logstore_xapi](https://github.com/xAPI-vle/moodle-logstore_xapi).

Designed for a customized version of OpenCart (based on version 2.3.0.2).

## Limitations and caveats

- This plugin identifies users to the LRS by mbox (email).
  - This assumes an identical email between Moodle, OpenCart and the Reader each user.
  - If an email is invalid, this plugin uses the OpenCart customer id, whereas the other systems use their own distinct user ids. Thus, such users will not have their xAPI statements from different origins automatically associated in the LRS.
- English only
  - While OpenCart can be set up to be multilingual, this plugin will currently only work for orders and products with language_id=1 (en-gb).
- Coupons...

## Usage

1) Install the plugin.
2) Install `php-cgi` (`sudo apt-get install php5-cgi`) on the OpenCart server.
3) Set up a cron to run `sudo php-cgi -f /var/www/html/opencart2.3/upload/index.php route=extension/module/logstore_xapi` every 5 minutes.
