<?php
class ControllerExtensionModuleAntispamByCleantalk extends Controller {
    private $error = array();

    public function index()
    {
        $this->install();

        $this->registry->set( 'apbct', AntispamByCleantalk\Core::get_instance( $this->registry ) );

        $data = $this->load->language('extension/module/antispambycleantalk');

        $this->document->setTitle($this->language->get('heading_title'));

        $this->load->model('setting/setting');

        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
            if (isset($this->request->post['module_antispambycleantalk_enable_sfw']) && isset($this->request->post['module_antispambycleantalk_access_key'])) {
                $this->apbct->sfw->sfw_update($this->request->post['module_antispambycleantalk_access_key']);
                $this->apbct->sfw->logs__send($this->request->post['module_antispambycleantalk_access_key']);
                $this->request->post['module_antispambycleantalk_int_sfw_last_check'] = time();
                $this->request->post['module_antispambycleantalk_int_sfw_last_send_logs'] = time();
            }
            $this->model_setting_setting->editSetting('module_antispambycleantalk', $this->request->post);

            $this->session->data['success'] = $this->language->get('text_success');

            $this->response->redirect($this->url->link('extension/extension', 'token=' . $this->session->data['token'] . '&type=module', true));
        }

        if (isset($this->error['warning'])) {
            $data['error_warning'] = $this->error['warning'];
        } else {
            $data['error_warning'] = '';
        }

        $data['breadcrumbs'] = array();

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/dashboard', 'token=' . $this->session->data['token'], true)
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_extension'),
            'href' => $this->url->link('marketplace/extension', 'token=' . $this->session->data['token'] . '&type=module', true)
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('extension/module/antispambycleantalk', 'token=' . $this->session->data['token'], true)
        );

        $data['action'] = $this->url->link('extension/module/antispambycleantalk', 'token=' . $this->session->data['token'], true);

        $data['cancel'] = $this->url->link('marketplace/extension', 'token=' . $this->session->data['token'] . '&type=module', true);

        if (isset($this->request->post['module_antispambycleantalk_status'])) {
            $data['module_antispambycleantalk_status'] = $this->request->post['module_antispambycleantalk_status'];
        } else {
            $data['module_antispambycleantalk_status'] = $this->config->get('module_antispambycleantalk_status');
        }
        if (isset($this->request->post['module_antispambycleantalk_check_registrations'])) {
            $data['module_antispambycleantalk_check_registrations'] = $this->request->post['module_antispambycleantalk_check_registrations'];
        } else {
            $data['module_antispambycleantalk_check_registrations'] = $this->config->get('module_antispambycleantalk_check_registrations');
        }
        if (isset($this->request->post['module_antispambycleantalk_check_orders'])) {
            $data['module_antispambycleantalk_check_orders'] = $this->request->post['module_antispambycleantalk_check_orders'];
        } else {
            $data['module_antispambycleantalk_check_orders'] = $this->config->get('module_antispambycleantalk_check_orders');
        }
        if (isset($this->request->post['module_antispambycleantalk_check_return'])) {
            $data['module_antispambycleantalk_check_return'] = $this->request->post['module_antispambycleantalk_check_return'];
        } else {
            $data['module_antispambycleantalk_check_return'] = $this->config->get('module_antispambycleantalk_check_return');
        }
        if (isset($this->request->post['module_antispambycleantalk_check_contact_form'])) {
            $data['module_antispambycleantalk_check_contact_form'] = $this->request->post['module_antispambycleantalk_check_contact_form'];
        } else {
            $data['module_antispambycleantalk_check_contact_form'] = $this->config->get('module_antispambycleantalk_check_contact_form');
        }
        if (isset($this->request->post['module_antispambycleantalk_check_reviews'])) {
            $data['module_antispambycleantalk_check_reviews'] = $this->request->post['module_antispambycleantalk_check_reviews'];
        } else {
            $data['module_antispambycleantalk_check_reviews'] = $this->config->get('module_antispambycleantalk_check_reviews');
        }
        if (isset($this->request->post['module_antispambycleantalk_enable_sfw'])) {
            $data['module_antispambycleantalk_enable_sfw'] = $this->request->post['module_antispambycleantalk_enable_sfw'];
        } else {
            $data['module_antispambycleantalk_enable_sfw'] = $this->config->get('module_antispambycleantalk_enable_sfw');
        }
        if (isset($this->request->post['module_antispambycleantalk_access_key'])) {
            $data['module_antispambycleantalk_access_key'] = $this->request->post['module_antispambycleantalk_access_key'];
        } elseif($this->config->get('module_antispambycleantalk_access_key')) {
            $data['module_antispambycleantalk_access_key'] = $this->config->get('module_antispambycleantalk_access_key');
        }else {
            $data['module_antispambycleantalk_access_key'] = '';
        }

        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');

        $this->response->setOutput($this->load->view('extension/module/antispambycleantalk', $data));
    }

    public function install(){
        $this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "cleantalk_sfw` (
            `network` int(10) unsigned NOT NULL, 
            `mask` int(10) unsigned NOT NULL, 
            `status` TINYINT(1) NOT NULL DEFAULT 0,
		    INDEX (  `network` ,  `mask` )
		)");
        $this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "cleantalk_sfw_logs` (
            `ip` varchar(15) NOT NULL, 
            `all_entries` int(11) NOT NULL, 
            `blocked_entries` int(11) NOT NULL, 
            `entries_timestamp` int(11) NOT NULL, 
            PRIMARY KEY `ip` (`ip`)
		)");
    }

    public function uninstall(){
        $this->load->model('setting/setting');
        $this->model_setting_setting->deleteSetting('module_antispambycleantalk');
        $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "cleantalk_sfw`");
        $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "cleantalk_sfw_logs`");
    }

    protected function validate() {
        if (!$this->user->hasPermission('modify', 'extension/module/antispambycleantalk')) {
            $this->error['warning'] = $this->language->get('error_permission');
        }

        return !$this->error;
    }
}