<?php
    // catalog/controller/extension/module/logstore_xapi.php
    class ControllerExtensionModuleLogstoreXapi extends Controller {

        private function get_event_function_map_altered() {
            require('catalog/controller/extension/module/logstore_xapi/get_event_function_map.php');
            $get_event_function_map_altered = array();
            foreach ($get_event_function_map as $key => $value) {
              $get_event_function_map_altered[mb_ereg_replace('^[^/]+/[^/]+/(.*)/[^/]+$', '\\1', $key)] = $value;
            }
            return $get_event_function_map_altered;
        }

        public function index() {
          // This function used to make the batch send to the LRS.

          // make sure this is coming from the cron
          if (php_sapi_name() != 'cgi-fcgi') {
            echo "<h1>Forbidden.</h1>";
            return;
          }

          date_default_timezone_set('Europe/London');
          $eventfunctionmap = $this->get_event_function_map_altered();

          $general = [
            'source_name' => 'OpenCart',
            'info_extension' => 'http://lrs.learninglocker.net/define/extensions/info',
            'db' => $this->db,
            'moodle_url_template' => "https://learn.biblemesh.com/course/view.php?id=MOODLE_ID",
            'site_base' => $this->config->get('site_base'),
            'config_name' => $this->config->get('config_name'),
          ];
      
          // get the extension configuration
          $endpoint = $this->config->get('logstore_xapi_endpoint');
          $username = $this->config->get('logstore_xapi_username');
          $password = $this->config->get('logstore_xapi_password');
          $max_batch_size = $this->config->get('logstore_xapi_max_batch_size');
      
          echo "Beginning Logstore xAPI batch send...\n";
      
          if(!$endpoint || !$username || !$max_batch_size) {
            echo "  Logstore xAPI not setup.\n";
            return false;
          }
      
          echo "  [ Endpoint: " . $endpoint . " ]\n";
          echo "  [ Username: " . $username . " ]\n";
          echo "  [ Maximum batch size: " . $max_batch_size . " ]\n";
      
          // get max batch size of rows
          $query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "logstore_xapi_log` LIMIT " . $max_batch_size);
          
          // turn them into xapi statements
          $statements = [];
          $ids_to_delete = [];
          foreach ($query->rows as $log) {
            if (isset($eventfunctionmap[$log['event_route']])) {
              require_once('catalog/controller/extension/module/logstore_xapi/' . $eventfunctionmap[$log['event_route']] . '.php');
              $eventfunction = $eventfunctionmap[$log['event_route']];
              $result = $eventfunction($log, $general);
              if($result) {
                  $statements = array_merge($statements, $result);
                  $ids_to_delete[] = $log['logstore_xapi_log_id'];
              }
            }
          }

          if (count($statements) === 0) {
            echo "  Nothing to send.\n";
            return;
          }
      
          echo "  Sending " . count($statements) . " statements...\n";
          print_r($statements);
          return;
      
          // send them to the store
          $url = mb_ereg_replace('(/statements|/)?$', '/', $endpoint) . '/statements';
          $auth = base64_encode($username.':'.$password);
          $postdata = json_encode($statements);
      
          $request = curl_init();
          curl_setopt($request, CURLOPT_URL, $url);
          curl_setopt($request, CURLOPT_POSTFIELDS, $postdata);
          curl_setopt($request, CURLOPT_RETURNTRANSFER, true);
          curl_setopt($request, CURLOPT_SSL_VERIFYPEER, false);
          curl_setopt($request, CURLOPT_HTTPHEADER, [
              'Authorization: Basic '.$auth,
              'X-Experience-API-Version: 1.0.0',
              'Content-Type: application/json',
          ]);
      
          $responsetext = curl_exec($request);
          $responsecode = curl_getinfo($request, CURLINFO_RESPONSE_CODE);
          curl_close($request);
      
          if ($responsecode !== 200) {
              echo "  Send failed: " . $responsetext . "\n";
              return;
          }
      
          echo "  Send succeeded.\n";
          echo "  Deleting log rows...\n";
      
          // delete rows that were successfully sent off
          $this->db->query("DELETE FROM `" . DB_PREFIX . "logstore_xapi_log` WHERE logstore_xapi_log_id IN (" . implode(",", $ids_to_delete) . ")");
          
          echo "  Delete successful.\n";
          echo "  Logstore xAPI batch send COMPLETE.\n";
        }

        public function store($route, $data) {
            $this->db->query("INSERT INTO `" . DB_PREFIX . "logstore_xapi_log`
                (
                    `event_route`,
                    `customer_id`,
                    `data`
                )
                VALUES (
                    '" . $this->db->escape($route) . "',
                    '" . $this->db->escape($this->customer->getId()) . "',
                    '" . $this->db->escape(json_encode($data)) . "'
                )
            ");
        }

    }
?>