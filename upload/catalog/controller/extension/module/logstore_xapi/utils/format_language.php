<?php
  function format_language($language) {

    $preppedlang = mb_ereg_replace('_', '-', $language);
    
    return mb_ereg_match('^[a-zA-Z]{2}(-[a-zA-Z]{2})?$', $preppedlang) ? $preppedlang : 'en';

  }
?>