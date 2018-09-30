<?php
  function get_basic_context($general) {

    return [
      "platform" => "OpenCart",
      "language" => $general['config_language'],
    ];
  }
?>