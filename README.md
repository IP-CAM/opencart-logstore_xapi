# opencart-logstore_xapi

## ToDos

- get basic func working
- Add in all other needed info
- Do other events (added: Average number of months a student pays for the institute subscription before they cancel it [50% likelihood])

- for LRS
  - Reduce IP addresses that can connect to only the LRS and me
  - Downgrade to M10

## Usage

1) Install `php-cgi` (`sudo apt-get install php5-cgi`)
2) Set up a cron to run `sudo php-cgi -f /var/www/html/opencart2.3/upload/index.php route=extension/module/logstore_xapi` every 5 minutes.

cd /var/www/html/opencart2.3/upload
> catalog/controller/extension/module/logstore_xapi/purchase.php && sudo nano catalog/controller/extension/module/logstore_xapi/purchase.php



  // What if course name is different than in Moodle - do they still get associated?
  // non-course product purchases
  // check order status before sending this over - make sure the transaction was approved!!
  // other events
    // recurring transaction
    // cancel recurring
  // look through order and order_product for other info we might want

  // All students from a particular category on opencart (reflecting the academic discipline)
    // category_description - name
    // partner (called that on frontend) atlas, etc are not listed on course page - might be called manufacturers
  // All students from a particular partner e.g. Pioneers, Crosslands (‘customer group’ in opencart, tenant, affiliates/referral)
    // customer_group_description - name
    // contextActivities > grouping > id :: DONE
    // eg. https://courses.crosslands.training/ should be distiguishable
    // affliliate category (eg. atlas, ebooks) look under catalog > affiliate partner
  // All students with a course expiring in the next 2 months
    // product tells timeframe to moodle, product > option > registration period / renewal period
  // Subscriptions live?
    // BibleMesh institute > reocurring (OpenCart does not have the history of transactions except for the first, but does get alerted to a cancelation; Moodle gets notified upon every renewal.)
  // How much revenue has a certain course brought in? ($$ used at purchase only? How do coupons/credits work, or purchases via institutions?)
    // order - price? total?
    // institutions are problematic because they are entered manually and invoiced in a way that OpenCart knows nothing about.
  // How many people received or used a certain discount
    // coupon_history - order_id / coupon - name

- order_product > product_to_category > id, category_description: name (academic discipline)
  - contextActivities grouping
- order_product > product > manufacturer: id, name (partner)
  - contextActivities parent
- order > customer_group_description: id, name (import for institution)
  - contextActivities other
- order > affiliate: id, firstname, lastname (affiliate partner)
  - context extensions
- order_product + product_option_value > product_option_value_to_moodle_period: today's date + interval * unit (expire date)
  - how to determine renewal vs regular??
  - take recurring into account
  - how to determine if they bought 6 month or 12 month access??
  - test with https://courses.biblemesh.com/hebrew/hebrew-first-steps-reading-1-bundle
  - object extension (OR result duration)
- order + order_product > order_recurring: recurring_id, recurring_name
  - object extension
- order_product: price
  - object extension OR (result raw score)
- order + order_product > coupon_history / coupon_product_history > coupon: id, name, code
  - (contextActivities other OR) context extension



