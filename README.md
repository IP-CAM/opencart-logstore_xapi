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

## Setting up book meta data in OpenCart

#### 1) Create several new Attributes in OpenCart called the following:
  - Readium Book Info
  - Readium Book Title
  - Readium Book Author
  - Readium Book Publisher

#### 2) For each product with MORE THAN ONE ebook related to it, give the Readium Book Info attribute a value in the following format.

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
* use the code editing mode </> in the attribute editor and toggle it off before saving, as is done with the “Readium Book ID” attribute

#### 3) For each product with ONLY ONE ebook related to it, add the title, author and publisher into the other new attribute fields (Readium Book Title, etc.). Add the ISBN under the corresponding field in the Data tab on the Edit product page.

Final note: All products with any ebooks (one or more) still need the “Readium Book ID” attribute
