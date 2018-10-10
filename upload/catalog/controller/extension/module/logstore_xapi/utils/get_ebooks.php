<?php
  function get_ebooks($order_product_row, $general) {

    // get the ebooks included in product
    $product_attribute_row = $general['db']->query(
      "SELECT pa.text FROM `" . DB_PREFIX . "product_attribute` as pa " .
      "LEFT JOIN `" . DB_PREFIX . "attribute` as a ON (pa.attribute_id=a.attribute_id) " .
      "LEFT JOIN `" . DB_PREFIX . "attribute_description` as ad " .
        "ON (pa.attribute_id=ad.attribute_id AND ad.language_id='" . $general['language_id'] . "') " .
      "WHERE product_id='" . $general['db']->escape($order_product_row['product_id']) . "' " .
        "AND ad.name='" . $general['ebook_attribute_description_name'] . "'"
    )->row;

    $includedEbookInfo = array();
    $badInfo = false;

    if($product_attribute_row) {
      $product_attribute_row_lines = mb_split("\n", $product_attribute_row['text']);
      for($i=0; $i<count($product_attribute_row_lines); $i++) {
        $ebookInfo = array();
        while($i<count($product_attribute_row_lines)) {
          if(trim($product_attribute_row_lines[$i]) === '') break;

          $infoPieces = mb_split("=", $product_attribute_row_lines[$i++], 2);
          $infoKey = mb_strtolower($infoPieces[0]);
          $infoValue = trim($infoPieces[1]);

          if(count($infoPieces) !== 2 || mb_strlen($infoValue) === 0) {
            $badInfo = true;
          }

          if(isset($ebookInfo[$infoKey])) {
            $badInfo = true;
          }

          if($infoKey === 'id') {
            $ebookInfo['id'] = mb_ereg_replace('EBOOK_ID', $infoValue, $general['ebook_url_template']);
          } else if(in_array($infoKey, ['author', 'publisher', 'isbn', 'title'])) {
            $ebookInfo[$infoKey] = $infoValue;
          }
        }

        if(isset($ebookInfo['id'])) {
          $includedEbookInfo[] = $ebookInfo;
        } else if(count($ebookInfo) > 0) {
          $badInfo = true;
        }
      }
    }

    if($badInfo) {
      echo "    Invalid ebook info for product id " . $order_product_row['product_id'] . ". Ignoring book info.\n";
      return;
    }

    return $includedEbookInfo;
  }
?>