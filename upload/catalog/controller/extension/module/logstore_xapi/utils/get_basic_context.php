<?php
  function get_basic_context($general) {

    return [
      "platform" => "OpenCart",
      "language" => $general['language_code'],
    ];
  }
?>