<?php
  function get_ebooks($order_product_row, $general) {

    // get the ebooks included in product
    $product_attribute_rows = $general['db']->query(
      "SELECT pa.text, ad.name FROM `" . DB_PREFIX . "product_attribute` as pa " .
      "LEFT JOIN `" . DB_PREFIX . "attribute` as a ON (pa.attribute_id=a.attribute_id) " .
      "LEFT JOIN `" . DB_PREFIX . "attribute_description` as ad " .
        "ON (pa.attribute_id=ad.attribute_id AND ad.language_id='" . $general['language_id'] . "') " .
      "WHERE product_id='" . $general['db']->escape($order_product_row['product_id']) . "' " .
        "AND ad.name LIKE '" . $general['ebook_attributes_description_name_prefix'] . "%'"
    )->rows;

    // get the product's isbn
    $product_row = $general['db']->query(
      "SELECT p.isbn FROM `" . DB_PREFIX . "product` as p " .
      "WHERE p.product_id='" . $general['db']->escape($order_product_row['product_id']) . "'"
    )->row;

    // get info from info attribute if exists, otherwise from individual attributes
    $multipleEbooksInfoInFormation = array();
    foreach($product_attribute_rows as $product_attribute_row) {
      $infoKey = mb_strtolower(mb_substr($product_attribute_row['name'], mb_strlen($general['ebook_attributes_description_name_prefix'])));
      if($infoKey === "info") {
        $multipleEbooksInfo = $product_attribute_row['text'];
        break;
      }
      $multipleEbooksInfoInFormation[] = $infoKey . "=" . strip_tags(html_entity_decode($product_attribute_row['text']));
    }
    if(!isset($multipleEbooksInfo)) {
      if($product_row && $product_row['isbn']) {
        $multipleEbooksInfoInFormation[] = "isbn=" . $product_row['isbn'];
      }
      $multipleEbooksInfo = implode("\n", $multipleEbooksInfoInFormation);
    }

    $includedEbookInfo = array();
    $badInfo = false;

    if($multipleEbooksInfo) {
      $productAttributeRowLines = mb_split("\n", $multipleEbooksInfo);
      for($i=0; $i<count($productAttributeRowLines); $i++) {
        $ebookInfo = array();
        while($i<count($productAttributeRowLines)) {
          if(trim($productAttributeRowLines[$i]) === '') break;

          $infoPieces = mb_split("=", $productAttributeRowLines[$i++], 2);
          $infoKey = mb_strtolower($infoPieces[0]);
          $infoValue = trim($infoPieces[1]);

          if(count($infoPieces) !== 2 || mb_strlen($infoValue) === 0) {
            $badInfo = true;
          }

          if(isset($ebookInfo[$infoKey]) && $infoKey !== 'isbn') {
            // isbn is an exception because they may have READIUM_BOOK_ISBN and ISBN both set.
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