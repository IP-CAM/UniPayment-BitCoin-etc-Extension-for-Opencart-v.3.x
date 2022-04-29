<?php
class ControllerExtensionPaymentUnipayment extends Controller {
	private $error = array(); 

	public function index() {
		$this->load->language('extension/payment/unipayment');

		$this->document->setTitle($this->language->get('heading_title'));
		
		$this->load->model('setting/setting');
		if (($this->request->server['REQUEST_METHOD'] == 'POST') && ($this->validate())) {
			$this->load->model('setting/setting');
			
			$this->model_setting_setting->editSetting('payment_unipayment', $this->request->post);				
			
			$this->session->data['success'] = $this->language->get('text_success');

			$this->response->redirect($this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=payment', true));
		}

 		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}

 		if (isset($this->error['app_key'])) {
			$data['error_app_key'] = $this->error['app_key'];
		} else {
			$data['error_app_key'] = '';
		}

 		if (isset($this->error['app_id'])) {
			$data['error_app_id'] = $this->error['app_id'];
		} else {
			$data['error_app_id'] = '';
		}

  		$data['breadcrumbs'] = array();

   		$data['breadcrumbs'][] = array(
       		'text'      => $this->language->get('text_home'),
			'href'      => $this->url->link('common/home', 'user_token=' . $this->session->data['user_token'], true),
   		);

   		$data['breadcrumbs'][] = array(
       		'text'      => $this->language->get('text_payment'),
			'href'      => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token']. '&type=payment', true),
   		);

   		$data['breadcrumbs'][] = array(
       		'text'      => $this->language->get('heading_title'),
			'href'      => $this->url->link('extension/payment/unipayment', 'user_token=' . $this->session->data['user_token']. '&type=payment', true),
   		);
		
		$data['action'] = $this->url->link('extension/payment/unipayment', 'user_token=' . $this->session->data['user_token'], true);
		
		$data['cancel'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token']. '&type=payment', true);
		
		if (isset($this->request->post['payment_unipayment_app_key'])) {
			$data['payment_unipayment_app_key'] = $this->request->post['payment_unipayment_app_key'];
		} else {
			$data['payment_unipayment_app_key'] = $this->config->get('payment_unipayment_app_key');
		}
		
		if (isset($this->request->post['payment_unipayment_app_id'])) {
			$data['payment_unipayment_app_id'] = $this->request->post['payment_unipayment_app_id'];
		} else {
			$data['payment_unipayment_app_id'] = $this->config->get('payment_unipayment_app_id');
		}

		if (isset($this->request->post['payment_unipayment_total'])) {
			$data['payment_unipayment_total'] = $this->request->post['payment_unipayment_total'];
		} else {
			$data['payment_unipayment_total'] = $this->config->get('payment_unipayment_total');
		}
		
		if (isset($this->request->post['payment_unipayment_geo_zone_id'])) {
			$data['payment_unipayment_geo_zone_id'] = $this->request->post['payment_unipayment_geo_zone_id'];
		} else {
			$data['payment_unipayment_geo_zone_id'] = $this->config->get('payment_unipayment_geo_zone_id'); 
		} 
		
		$this->load->model('localisation/geo_zone');
										
		$data['geo_zones'] = $this->model_localisation_geo_zone->getGeoZones();

		if (isset($this->request->post['payment_unipayment_sandbox'])) {
			$data['payment_unipayment_sandbox'] = $this->request->post['payment_unipayment_sandbox'];
		} else {
			$data['payment_unipayment_sandbox'] = $this->config->get('payment_unipayment_sandbox'); 
		}

		if (isset($this->request->post['payment_unipayment_debug'])) {
			$data['payment_unipayment_debug'] = $this->request->post['payment_unipayment_debug'];
		} else {
			$data['payment_unipayment_debug'] = $this->config->get('payment_unipayment_debug'); 
		}

		if (isset($this->request->post['payment_unipayment_currency'])) {
			$data['payment_unipayment_currency'] = $this->request->post['payment_unipayment_currency'];
		} else {
			$data['payment_unipayment_currency'] = $this->config->get('payment_unipayment_currency'); 
		}

		$this->load->model('localisation/currency');

		$data['currencies'] = $this->model_localisation_currency->getCurrencies();

		if (isset($this->request->post['payment_unipayment_paid_order_status_id'])) {
			$data['payment_unipayment_paid_order_status_id'] = $this->request->post['payment_unipayment_paid_order_status_id'];
		} else {
			$data['payment_unipayment_paid_order_status_id'] = $this->config->get('payment_unipayment_paid_order_status_id'); 
		}
		if (isset($this->request->post['payment_unipayment_confirmed_order_status_id'])) {
			$data['payment_unipayment_confirmed_order_status_id'] = $this->request->post['payment_unipayment_confirmed_order_status_id'];
		} else {
			$data['payment_unipayment_confirmed_order_status_id'] = $this->config->get('payment_unipayment_confirmed_order_status_id'); 
		} 

		if (isset($this->request->post['payment_unipayment_complete_order_status_id'])) {
			$data['payment_unipayment_complete_order_status_id'] = $this->request->post['payment_unipayment_complete_order_status_id'];
		} else {
			$data['payment_unipayment_complete_order_status_id'] = $this->config->get('payment_unipayment_complete_order_status_id'); 
		} 

		if (isset($this->request->post['payment_unipayment_expired_order_status_id'])) {
			$data['payment_unipayment_expired_order_status_id'] = $this->request->post['payment_unipayment_expired_order_status_id'];
		} else {
			$data['payment_unipayment_expired_order_status_id'] = $this->config->get('payment_unipayment_expired_order_status_id'); 
		}

		if (isset($this->request->post['payment_unipayment_invalid_order_status_id'])) {
			$data['payment_unipayment_invalid_order_status_id'] = $this->request->post['payment_unipayment_invalid_order_status_id'];
		} else {
			$data['payment_unipayment_invalid_order_status_id'] = $this->config->get('payment_unipayment_invalid_order_status_id'); 
		}   

		$this->load->model('localisation/order_status');
		$data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();
		
		if (isset($this->request->post['payment_unipayment_status'])) {
			$data['payment_unipayment_status'] = $this->request->post['payment_unipayment_status'];
		} else {
			$data['payment_unipayment_status'] = $this->config->get('payment_unipayment_status');
		}
		
		if (isset($this->request->post['payment_unipayment_sort_order'])) {
			$data['payment_unipayment_sort_order'] = $this->request->post['payment_unipayment_sort_order'];
		} else {
			$data['payment_unipayment_sort_order'] = $this->config->get('payment_unipayment_sort_order');
		}
	
		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('extension/payment/unipayment', $data));
	}

	private function validate() {
		if (!$this->user->hasPermission('modify', 'extension/payment/unipayment')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}
		
	   	if (!$this->request->post['payment_unipayment_app_key']) {
			$this->error['app_key'] = $this->language->get('error_app_key');
		}        

		if (!$this->request->post['payment_unipayment_app_id']) {
			$this->error['app_id'] = $this->language->get('error_app_id');
		}

		return !$this->error;
	}
}
?>