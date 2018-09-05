<?php
  function get_platform($general) {

    return [
      "id" => "https://opencart.com",
      "definition" => [
        "type" => "http://id.tincanapi.com/activitytype/source",
        "name" => [
          $general['config_language'] => "OpenCart",
        ],
      ],
      "objectType" => "Activity",
    ];
  }
?>