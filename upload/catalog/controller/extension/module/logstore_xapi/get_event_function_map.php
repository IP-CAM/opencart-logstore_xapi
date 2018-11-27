<?php
  $get_event_function_map = [
    'catalog/model/checkout/order/addOrderHistory/after' => 'purchase',
    'catalog/model/extension/payment/pp_express/log/after' => 'recurringTransaction',
    'catalog/model/account/recurring/addOrderRecurringTransaction/before' => 'recurringTransaction',
  ];
?>