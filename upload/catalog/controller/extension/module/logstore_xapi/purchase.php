<?php
  function purchase($log, $general) {
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