# opencart-logstore_xapi

Built in similar fashion to [moodle-logstore_xapi](https://github.com/xAPI-vle/moodle-logstore_xapi).

Designed for a customized version of OpenCart (based on version 2.3.0.2).

## Limitations and caveats

- This plugin identifies users to the LRS by mbox (email).
  - This assumes an identical email between Moodle, OpenCart and the Reader for each user.
  - If an email is invalid, this plugin uses the OpenCart customer id, whereas the other systems use their own distinct user ids. Thus, such users will not have their xAPI statements from different origins automatically associated in the LRS.
- English only
  - While OpenCart can be set up to be multilingual, this plugin will currently only work for orders and products with language_id=1 (en-gb).
- Coupons are listed as a part of the order under `extensions` and reduced from the `cost` of the statement's specific item as accurately as possible. (OpenCart's coupon functionality makes it extremely difficult to know which products a coupon is applied to in multi-product orders with certain types of coupons.) 
- Specials and discounts are not distinguished, but simply reflected in the `price` and `cost` of the product. (Again, OpenCart's handling of these makes it nearly impossible to be more precise.)

## Installation and usage

1) Set up book meta data. (see below)
2) Copy in the files from this repo.
3) Install the plugin and edit its settings appropriately.
4) Create xapi logs from past orders. (see below)
5) Install `php-cgi` (`sudo apt-get install php5-cgi`) on the OpenCart server (needed for the cron).
6) Set up a cron to run `sudo php-cgi -f /var/www/html/opencart2.3/upload/index.php route=extension/module/logstore_xapi` every 5 minutes.

## Create xapi logs for past orders (and recurring transactions)

Replace `comma-separated-test-customer-ids-here` and `date-and-time-of-install-here` appropriately prior to running these on the DB.

```sql
INSERT INTO oc_logstore_xapi_log (event_route, data, customer_id, date_added)
  (
    SELECT
      'checkout/order/addOrderHistory',
      CONCAT('[', order_id, ',"', order_status_id, '"]'),
      customer_id,
      date_added
    FROM oc_order
    WHERE 
      customer_id NOT IN ( comma-separated-test-customer-ids-here )
      AND order_status_id = 5
      AND date_added < "date-and-time-of-install-here"
  )
```

```sql
INSERT INTO oc_logstore_xapi_log (event_route, data, customer_id, date_added)
  (
    SELECT
      'extension/payment/pp_express/log',
      CONCAT('[{"request":"txn_type=', IF(ort.type=1, 'recurring_payment', IF(ort.type=4, 'recurring_payment_failed', 'recurring_payment_profile_cancel')), '&rp_invoice_id=', ort.order_recurring_id, '&payment_gross=', ort.amount, '","response":"VERIFIED"},"IPN data"]'),
      o.customer_id,
      ort.date_added
    FROM oc_order_recurring_transaction as ort
    	LEFT JOIN oc_order_recurring as or1 ON (or1.order_recurring_id = ort.order_recurring_id)
    	LEFT JOIN oc_order as o ON (o.order_id = or1.order_id)
    WHERE 
      o.customer_id NOT IN ( comma-separated-test-customer-ids-here )
      AND o.order_status_id = 5
      AND ort.type IN (1, 4, 5)
      AND ort.date_added < "date-and-time-of-install-here"
  )
```

## Setting up book meta data in OpenCart

#### 1) Create several new Attributes in OpenCart called the following:
  - Readium Book Info
  - Readium Book Title
  - Readium Book Author
  - Readium Book Publisher

#### 2) For each product WHICH IS NOT A PRODUCT IN AND OF ITSELF, give the `Readium Book Info` attribute a value in the following format.

```
ID=35
TITLE=Greek for the Rest of Us: The Essentials of Biblical Greek, Second Edition
AUTHOR=William D. Mounce
PUBLISHER=HarperCollins Christian Publishing
ISBN=9780310518099

ID=36
TITLE=A Theology of James, Peter, and Jude
AUTHOR=Peter H. Davids
PUBLISHER=HarperCollins Christian Publishing
ISBN=9780310519430
```

Notes:

* the ID is the reader id
* an empty line must be between books
* use the code editing mode </> in the attribute editor and toggle it off before saving, as is done with the `Readium Book ID` attribute

#### 3) For each product WHICH IS ITSELF AN EBOOK, add the title, author and publisher into the other new attribute fields (`Readium Book Title`, etc.). Add the ISBN under the corresponding field in the Data tab on the Edit product page.

Final note: All products with any ebooks (one or more) still need the `Readium Book ID` attribute
