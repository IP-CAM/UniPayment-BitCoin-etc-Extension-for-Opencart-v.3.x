<?php
require_once(DIR_SYSTEM.'library/unipayment/vendor/autoload.php');

class ControllerExtensionPaymentUnipayment extends Controller {
	
	var $unipaymentTrace;
	
	private $error = array();
	private $basicsettingsok;
	private $posData;
	
	
	public function index() {
		$this->load->language('extension/payment/unipayment');
		$this->document->setTitle($this->language->get('heading_title'));
		$this->load->model('setting/setting');
		
		$currentPrimaryContrats = $this->config->get('payment_unipayment_primary_contracts');
		
		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {			
		
			$this->model_setting_setting->editSetting('payment_unipayment', $this->request->post);
			if($currentPrimaryContrats == null){
				$this->session->data['success'] = $this->language->get('text_incomplete');
			} else {
				$this->session->data['success'] = $this->language->get('text_success');
			}
			$this->response->redirect($this->url->link('extension/payment/unipayment', 'user_token=' . $this->session->data['user_token'], true));			
		}
		
		/*
		 * labels and fieldsets
		 */
		$data['heading_title']					= $this->language->get('heading_title');
		$data['text_enabled']						= $this->language->get('text_enabled');
		$data['text_disabled']					= $this->language->get('text_disabled');
		$data['text_live']					= $this->language->get('text_live');		
		$data['text_test']					= $this->language->get('text_test');
		$data['text_all_zones']					= $this->language->get('text_all_zones');
		
		
		$data['fieldset_unipayment_module']			= $this->language->get('fieldset_unipayment_module');		
		$data['fieldset_unipayment_payment']			= $this->language->get('fieldset_unipayment_payment');
		$data['fieldset_unipayment_contracts']		= $this->language->get('fieldset_unipayment_contracts');
		$data['fieldset_unipayment_basicsettings']	= $this->language->get('fieldset_unipayment_basicsettings');
		
		/*
		 * Module settings
		 */
		$data['entry_unipayment_status']	= $this->language->get('entry_unipayment_status');
		$data['entry_geo_zone']		= $this->language->get('entry_geo_zone');
		$data['entry_sort_order']		= $this->language->get('entry_sort_order');
		$data['entry_method_name']	= $this->language->get('entry_method_name');
		
		//helps
		$data['help_method_name']		= $this->language->get('help_method_name');
		$data['help_unipayment_status']	= $this->language->get('help_unipayment_status');
		$data['help_geo_zone']		= $this->language->get('help_geo_zone');
		$data['help_sort_order']		= $this->language->get('help_sort_order');
		$data['help_sort_order']		= $this->language->get('help_sort_order');		
		
			
		

		if (isset($this->request->post['payment_unipayment_status'])) {
			$data['payment_unipayment_status'] = $this->request->post['payment_unipayment_status'];
		} else {
			$data['payment_unipayment_status'] = $this->config->get('payment_unipayment_status');
		}
		$this->load->model('localisation/geo_zone');
		$data['geo_zones'] = $this->model_localisation_geo_zone->getGeoZones();
		if (isset($this->request->post['payment_unipayment_geo_zone_id'])) {
			$data['payment_unipayment_geo_zone_id'] = $this->request->post['payment_unipayment_geo_zone_id'];
		} else {
			$data['payment_unipayment_geo_zone_id'] = $this->config->get('payment_unipayment_geo_zone_id');
		}
		if (isset($this->request->post['payment_unipayment_sort_order'])) {
			$data['payment_unipayment_sort_order'] = $this->request->post['payment_unipayment_sort_order'];
		} else {
			$data['payment_unipayment_sort_order'] = $this->config->get('payment_unipayment_sort_order');
		}
		
		$method_name = $this->config->get('payment_unipayment_method_name');
		if (isset($this->request->post['payment_unipayment_method_name'])) {
			$data['payment_unipayment_method_name'] = $this->request->post['payment_unipayment_method_name'];
		} else {
			$data['payment_unipayment_method_name'] = (isset($method_name)) ? $this->config->get('payment_unipayment_method_name') : $this->language->get('heading_title');;
		}
		if (isset($this->error['method_name'])) {
			$data['error_method_name'] = $this->error['method_name'];
		} else {
			$data['error_method_name'] = '';
		}
		
		/*
		 * Unipayment connection 
		 */
		$data['entry_client_id']	= $this->language->get('entry_client_id');		
		$data['entry_client_secret']	= $this->language->get('entry_client_secret');				
		$data['entry_app_id']	= $this->language->get('entry_app_id');
		$data['entry_confirm_speed']	= $this->language->get('entry_confirm_speed');
		$data['entry_pay_currency']	= $this->language->get('entry_pay_currency');
		$data['entry_processing_status']	= $this->language->get('entry_processing_status');		
		$data['entry_handle_expired_status']	= $this->language->get('entry_handle_expired_status');	
		$data['entry_environment']	= $this->language->get('entry_environment');									
		
		$data['help_client_id']		= $this->language->get('help_client_id');		
		$data['help_client_secret']		= $this->language->get('help_client_secret');			
		$data['help_app_id']		= $this->language->get('help_app_id');			
		$data['help_confirm_speed']		= $this->language->get('help_confirm_speed');			
		$data['help_pay_currency']		= $this->language->get('help_pay_currency');			
		$data['help_processing_status']		= $this->language->get('help_processing_status');			
		$data['help_handle_expired_status']		= $this->language->get('help_handle_expired_status');					
		$data['help_environment']		= $this->language->get('help_environment');
		
		//errors
		if (isset($this->error['environment'])) {
			$data['error_environment'] = $this->error['environment'];
		} else {
			$data['error_environment'] = '';
		}
		if (isset($this->error['client_id'])) {
			$data['error_client_id'] = $this->error['client_id'];
		} else {
			$data['error_client_id'] = '';
		}
		if (isset($this->error['client_secret'])) {
			$data['error_client_secret'] = $this->error['client_secret'];
		} else {
			$data['error_token'] = '';
		}
		
		if (isset($this->error['app_id'])) {
			$data['error_app_id'] = $this->error['app_id'];
		} else {
			$data['error_app_id'] = '';
		}
		
		
		
		if (isset($this->request->post['payment_unipayment_client_id'])) {
			$data['payment_unipayment_client_id'] = $this->request->post['payment_unipayment_client_id'];
		} else {
			$data['payment_unipayment_client_id'] = $this->config->get('payment_unipayment_client_id');
		}
		
		if (isset($this->request->post['payment_unipayment_client_secret'])) {
			$data['payment_unipayment_client_secret'] = $this->request->post['payment_unipayment_client_secret'];
		} else {
			$data['payment_unipayment_client_secret'] = $this->config->get('payment_unipayment_client_secret');
		}
		
		if (isset($this->request->post['payment_unipayment_app_id'])) {
			$data['payment_unipayment_app_id'] = $this->request->post['payment_unipayment_app_id'];
		} else {
			$data['payment_unipayment_app_id'] = $this->config->get('payment_unipayment_app_id');
		}
		
		if (isset($this->request->post['payment_unipayment_confirm_speed'])) {
			$data['payment_unipayment_confirm_speed'] = $this->request->post['payment_unipayment_confirm_speed'];
		} else {
			$confirm_speed = $this->config->get('payment_unipayment_confirm_speed'); 			
			$data['payment_unipayment_confirm_speed'] = (isset($confirm_speed)) ? $confirm_speed : 'medium';;
		}
		if (isset($this->request->post['payment_unipayment_pay_currency'])) {
			$data['payment_unipayment_pay_currency'] = $this->request->post['payment_unipayment_pay_currency'];
		} else {
			$pay_currency = $this->config->get('payment_unipayment_pay_currency'); 			
			$data['payment_unipayment_pay_currency'] = (isset($pay_currency)) ? $pay_currency : '-';
		}		

		if (isset($this->request->post['payment_unipayment_processing_status'])) {
			$data['payment_unipayment_processing_status'] = $this->request->post['payment_unipayment_processing_status'];
		} else {
			$processing_status = $this->config->get('payment_unipayment_processing_status'); 			
			$data['payment_unipayment_processing_status'] = (isset($processing_status)) ? $processing_status : '2';
		}		
		
		if (isset($this->request->post['payment_unipayment_handle_expired_status'])) {
			
			$data['payment_unipayment_handle_expired_status'] = $this->request->post['payment_unipayment_handle_expired_status'];
		} else {
			$handle_expired_status = $this->config->get('payment_unipayment_handle_expired_status');			
			$data['payment_unipayment_handle_expired_status'] = (isset($handle_expired_status)) ? $handle_expired_status : '0';
		}	
		
		if (isset($this->request->post['payment_unipayment_environment'])) {
			$data['payment_unipayment_environment'] = $this->request->post['payment_unipayment_environment'];
		} else {
			$environment= $this->config->get('payment_unipayment_environment');
			$data['payment_unipayment_environment'] =(isset($environment)) ? $environment : 'test';
		}
		
		//list data
		$this->load->model('localisation/order_status');
		
		$confirm_speeds = [];
		$confirm_speeds[] = array('id' => 'low', 'name' => 'low');
		$confirm_speeds[] = array('id' => 'medium', 'name' => 'medium');		
		$confirm_speeds[] = array('id' => 'high', 'name' => 'high');		
		$data['confirm_speeds'] = $confirm_speeds;
		
		$processing_statuses = [];
		$processing_statuses[] = array('name' => 'Confirmed');
		$processing_statuses[] = array('name' => 'Complete');	
		$processing_statuses[] = array('name' => 'Paid');
		
		$data['processing_statuses'] = $processing_statuses;
		
		
				
		
		
		$pay_currencies =  $this->get_currencies();
		$data['pay_currencies'] = $pay_currencies;		
		
		/*
		 * Buttons
		 */
		$data['button_save']		= $this->language->get('button_save');
		$data['button_cancel']	= $this->language->get('button_cancel');
		$data['button_search']	= $this->language->get('button_search');		

			

		
		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}
		
		/*
		 * Navigation
		 */
		$data['breadcrumbs'] = array();
		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
		);
		
		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_payment'),
			'href' => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=payment', true)
		);
		
		
		$data['breadcrumbs'][] = array(
		       		'text'      => $this->language->get('heading_title'),
					'href'      => $this->url->link('extension/payment/unipayment', 'user_token=' . $this->session->data['user_token'], true)
		);
		
		
		$data['action'] = $this->url->link('extension/payment/unipayment', 'user_token=' . $this->session->data['user_token'], true);
		$data['cancel'] = $this->url->link('extension/payment', 'user_token=' . $this->session->data['user_token'], true);
		

		$data['user_token'] = $this->session->data['user_token'];

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');		
			
		$this->response->setOutput($this->load->view('extension/payment/unipayment', $data));	
	}
	
	
	
	

	
	
	private function writeTrace($trace){
		if(!isset($this->unipaymentTrace)){
			$this->unipaymentTrace = new Log('payment_unipayment_'.date('Y-m-d',time()).'.txt');
		}
		$this->unipaymentTrace->write($trace);
	}
	
	
	private function validate() {
		
		$exit = false;

		
		if (!$this->user->hasPermission('modify', 'extension/payment/unipayment')) {
			$this->writeTrace("[ADMIN] - current user has no permission to edit configuration");
			$this->error['warning'] = $this->language->get('error_permission');
			$exit = true;
		}
		if (!$this->request->post['payment_unipayment_method_name']) {
			$this->writeTrace("[ADMIN] - Unipayment method name is missing");
			$this->error['warning'] = $this->language->get('error_config');
			$this->error['method_name'] = $this->language->get('required_method_name');
			$exit = true;
		}
		if (!$this->request->post['payment_unipayment_client_id']) {
			$this->writeTrace("[ADMIN] - Unipayment merchant ID is missing");
			$this->error['warning'] = $this->language->get('error_config');
			$this->error['client_id'] = $this->language->get('required_client_id');
			$exit = true;
		}
		if (!$this->request->post['payment_unipayment_client_secret']) {
			$this->writeTrace("[ADMIN] - Unipayment Client Secret is missing");
			$this->error['warning'] = $this->language->get('error_config');
			$this->error['client_secret'] = $this->language->get('required_client_secret');
			$exit = true;
		}
		
		if (!$this->request->post['payment_unipayment_app_id']) {
			$this->writeTrace("[ADMIN] - Unipayment App ID is missing");
			$this->error['warning'] = $this->language->get('error_config');
			$this->error['app_id'] = $this->language->get('required_app_id');
			$exit = true;
		}
		
		if ($exit) {
			return false;
		}
		$exit = false; // reset exit flag
		
		/*
		 * A sample web service (getMerchantSettings) call is done to check the configuration
		 */
		 
		
		if (!$exit) {
			return true;
		} else {
			return false;
		}
	}
	
	public function get_currencies($fiat = false)
        {
            $currencies = array();
            $uniPaymentClient = new \UniPayment\Client\UniPaymentClient();
            $uniPaymentClient->getConfig()->setDebug(false);
			$environment = $this->config->get('payment_unipayment_environment');
			$environment = (isset($environment)) ? $environment : 'test';
            $uniPaymentClient->getConfig()->setIsSandbox($environment == 'test');
		
            $apires = $uniPaymentClient->getCurrencies();
			$currencies[] = array('code'=>'-', 'name'=>'---');
            if ($apires['code'] == 'OK') {
                foreach ($apires['data'] as $crow) {
                    if ($crow['is_fiat'] == $fiat) $currencies[] = array('code'=>$crow['code'], 'name'=>$crow['code']);
                }
            }
            return $currencies;
        }	
}
?>