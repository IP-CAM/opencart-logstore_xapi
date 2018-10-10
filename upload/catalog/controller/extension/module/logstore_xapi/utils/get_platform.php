<?php
  function get_platform($general) {

    return [[
      "id" => "https://opencart.com",
      "definition" => [
        "type" => "http://id.tincanapi.com/activitytype/source",
        "name" => [
          $general['language_code'] => "OpenCart",
        ],
      ],
      "objectType" => "Activity",
    ]];
  }
?>