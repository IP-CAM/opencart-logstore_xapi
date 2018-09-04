<?php
  function purchase($log, $general) {

    // get the order id
    $data = json_decode($log['data']);
    $order_id = $data[0];

    if(!$order_id) {
      echo "    Invalid purchase log:\n";
      print_r($log);
      return;
    }

    // get the info needed from the DB
    $order_row = $general['db']->query("SELECT * FROM `" . DB_PREFIX . "order` WHERE order_id='" . $general['db']->escape($order_id) . "'")->row;

    if(!$order_row) {
      echo "    Cannot find order row for purchase:\n";
      print_r($log);
      return;
    }

    print_r($order_row);

    return [[
      'test' => 'good'
    ]];

    // $repo = $config['repo'];
    // $user = $repo->read_record_by_id('user', $event->relateduserid);
    // $course = $repo->read_record_by_id('course', $event->courseid);
    // $lang = utils\get_course_lang($course);  ??

    // return[[
    //     'actor' => utils\get_user($log['user_id']),
    //     'verb' => [
    //         'id' => '??',
    //         'display' => [
    //             $lang => 'purchased'
    //         ],
    //     ],
    //     'object' => utils\get_course(??),
    //     'timestamp' => date('c', $event['date_added']),
    //     'context' => [
    //         'platform' => $config['source_name'],
    //         'language' => $lang,
    //         'extensions' => [
    //             $general['info_extension'] => utils\get_info($event, $general),
    //         ],
    //         'contextActivities' => [
    //             'grouping' => [
    //                 utils\get_activity\site($config)
    //             ],
    //             'category' => [
    //                 utils\get_activity\source($config)
    //             ]
    //         ],
    //     ]
    // ]];

  }
?>