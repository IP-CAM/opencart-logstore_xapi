<?php
    // catalog/controller/extension/module/logstore_xapi.php
    class ControllerExtensionModuleLogstoreXapi extends Controller {
        public function index() {
        }

        public function store($route, $data) {
            $this->db->query("INSERT INTO `" . DB_PREFIX . "logstore_xapi_log`
                (
                    `event_route`,
                    `user_id`,
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