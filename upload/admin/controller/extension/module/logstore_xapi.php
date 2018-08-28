<?php
    // admin/controller/extension/module/logstore_xapi.php
    class ControllerExtensionModuleLogstoreXapi extends Controller {
        private $error = array();
    
        private $event_function_map = [
            'admin/view/extension/extension/before' => 'purchase',
        ];

        public function install() {
            $eventfunctionmapkeys = array_keys($this->event_function_map);

            // set up the database
            $this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "logstore_xapi_log` (
                `logstore_xapi_log_id` int(11) NOT NULL AUTO_INCREMENT,
                `event_route` varchar(255) NOT NULL,
                `data` longtext NOT NULL,
                `user_id` int(11) NOT NULL,
                `date_added` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (`logstore_xapi_log_id`)
            )");

            // add the events
            $this->load->model('extension/event');
            foreach($eventfunctionmapkeys as $idx => $eventroute) {
                $this->model_extension_event->addEvent('logstore_xapi_' . $idx, $eventroute, 'extension/module/logstore_xapi/store');
            }
        }

        public function uninstall() {
            $eventfunctionmapkeys = array_keys($this->event_function_map);

            // remove the database table
            $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "logstore_xapi_log`");

            // remove the events
            $this->load->model('extension/event');
            foreach($eventfunctionmapkeys as $idx => $eventroute) {
                $this->model_extension_event->deleteEvent('logstore_xapi_' . $idx);
            }
        }

        public function index() {
            $this->load->language('extension/module/html');
            $this->load->language('extension/module/logstore_xapi');
    
            $this->document->setTitle($this->language->get('heading_title'));
    
            $this->load->model('setting/setting');
    
            if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
                $this->model_setting_setting->editSetting('logstore_xapi', $this->request->post);
                    
                $this->session->data['success'] = $this->language->get('text_success');
        
                $this->response->redirect($this->url->link('extension/extension', 'token=' . $this->session->data['token'] . '&type=module', 'SSL'));
            }
    
            $data['heading_title'] = $this->language->get('heading_title');
    
            $data['text_edit'] = $this->language->get('text_edit');
    
            $data['entry_endpoint'] = $this->language->get('entry_endpoint');
            $data['entry_username'] = $this->language->get('entry_username');
            $data['entry_password'] = $this->language->get('entry_password');
            $data['entry_max_batch_size'] = $this->language->get('entry_max_batch_size');
    
            $data['button_save'] = $this->language->get('button_save');
            $data['button_cancel'] = $this->language->get('button_cancel');
    
            if (isset($this->error['warning'])) {
                $data['error_warning'] = $this->error['warning'];
            } else {
                $data['error_warning'] = '';
            }
    
            if (isset($this->error['endpoint'])) {
                $data['error_endpoint'] = $this->error['endpoint'];
            } else {
                $data['error_endpoint'] = '';
            }
    
            if (isset($this->error['max_batch_size'])) {
                $data['error_max_batch_size'] = $this->error['max_batch_size'];
            } else {
                $data['error_max_batch_size'] = '';
            }
    
            $data['breadcrumbs'] = array();
    
            $data['breadcrumbs'][] = array(
                'text' => $this->language->get('text_home'),
                'href' => $this->url->link('common/dashboard', 'token=' . $this->session->data['token'], 'SSL')
            );
    
            $data['breadcrumbs'][] = array(
                'text' => $this->language->get('text_extension'),
                'href' => $this->url->link('extension/extension', 'token=' . $this->session->data['token'] . '&type=module', 'SSL')
            );
    
            $data['breadcrumbs'][] = array(
                'text' => $this->language->get('heading_title'),
                'href' => $this->url->link('extension/module/logstore_xapi', 'token=' . $this->session->data['token'], 'SSL')
            );
    
            $data['cancel'] = $this->url->link('extension/extension', 'token=' . $this->session->data['token'] . '&type=module', 'SSL');

            if (isset($this->request->post['logstore_xapi_endpoint'])) {
                $data['logstore_xapi_endpoint'] = $this->request->post['logstore_xapi_endpoint'];
            } elseif($this->config->get('logstore_xapi_endpoint')) {
                $data['logstore_xapi_endpoint'] = $this->config->get('logstore_xapi_endpoint');
            } else {
                $data['logstore_xapi_endpoint'] = '';
            }
    
            if (isset($this->request->post['logstore_xapi_username'])) {
                $data['logstore_xapi_username'] = $this->request->post['logstore_xapi_username'];
            } elseif($this->config->get('logstore_xapi_username')) {
                $data['logstore_xapi_username'] = $this->config->get('logstore_xapi_username');
            } else {
                $data['logstore_xapi_username'] = '';
            }
    
            if (isset($this->request->post['logstore_xapi_password'])) {
                $data['logstore_xapi_password'] = $this->request->post['logstore_xapi_password'];
            } elseif($this->config->get('logstore_xapi_password')) {
                $data['logstore_xapi_password'] = $this->config->get('logstore_xapi_password');
            } else {
                $data['logstore_xapi_password'] = '';
            }
    
            if (isset($this->request->post['logstore_xapi_max_batch_size'])) {
                $data['logstore_xapi_max_batch_size'] = $this->request->post['logstore_xapi_max_batch_size'];
            } elseif($this->config->get('logstore_xapi_max_batch_size')) {
                $data['logstore_xapi_max_batch_size'] = $this->config->get('logstore_xapi_max_batch_size');
            } else {
                $data['logstore_xapi_max_batch_size'] = '';
            }
    
            $data['header'] = $this->load->controller('common/header');
            $data['column_left'] = $this->load->controller('common/column_left');
            $data['footer'] = $this->load->controller('common/footer');
    
            $this->response->setOutput($this->load->view('extension/module/logstore_xapi.tpl', $data));
        }
    
        protected function validate() {
            if (!$this->user->hasPermission('modify', 'extension/module/logstore_xapi')) {
                $this->error['warning'] = $this->language->get('error_permission');
            }
    
            if ($this->request->post['logstore_xapi_endpoint'] !== '' && !filter_var($this->request->post['logstore_xapi_endpoint'], FILTER_VALIDATE_URL)) {
                $this->error['endpoint'] = $this->language->get('error_endpoint');
            }
    
            if ($this->request->post['logstore_xapi_max_batch_size'] !== '' && !is_numeric($this->request->post['logstore_xapi_max_batch_size'])) {
                $this->error['max_batch_size'] = $this->language->get('error_max_batch_size');
            }
    
            return !$this->error;
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