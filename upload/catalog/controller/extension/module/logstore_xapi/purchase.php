<?php
  require_once('utils/get_customer.php');

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
    $actor = get_customer($log['customer_id'], $general);

    // see if we have all the info we need
    if(!$order_row) {
      echo "    Cannot find order row for purchase:\n";
      print_r($log);
      return;
    }
    if(!$actor) {
      echo "    Cannot find customer who made the purchase:\n";
      print_r($log);
      return;
    }

    print_r($log);
    print_r($order_row);

    return [[
      "actor" => $actor,
      "verb" => [
        "id" => "http://activitystrea.ms/schema/1.0/purchase",
        "display" => [
          "en" => "purchased",
        ],
      ],
      "object" => [
        "id" => "https://sandbox.biblemesh.com/course-catalog/christianity-explored-course",
        "definition" => [
          "type" => "http://id.tincanapi.com/activitytype/lms/course",
          "name" => [
            "en" => "Christianity Explored",
          ],
        ],
      ],
      "timestamp" => "2014-11-11T15:53:20+00:00",
      "context" => [
        "platform" => "OpenCart",
        "language" => "en",
        "extensions" => [
          "http://lrs.learninglocker.net/define/extensions/info" => [
            "https://opencart.com" => "2.3.0.2",
            "event_name" => "checkout\\order\\addOrderHistory",
            "event_function" => "purchase",
            "order_id" => 123,
          ],
        ],
        "contextActivities" => [
          "grouping" => [
            [
              "id" => "https://biblemesh.com",
              "definition" => [
                "type" => "http://activitystrea.ms/schema/1.0/service",
                "name" => [
                  "en" => "BibleMesh",
                ],
              ],
              "objectType" => "Activity"
            ],
          ],
          "category" => [
            [
              "id" => "https://opencart.com",
              "definition" => [
                "type" => "http://id.tincanapi.com/activitytype/source",
                "name" => [
                  "en" => "OpenCart",
                ],
              ],
              "objectType" => "Activity",
            ],
          ],
        ],
      ],
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