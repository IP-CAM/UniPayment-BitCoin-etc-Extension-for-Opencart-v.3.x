<?php

require_once(DIR_SYSTEM.'library/unipayment/vendor/autoload.php');

class ControllerExtensionPaymentUnipayment extends Controller {
	
	var $unipaymentTrace, $errorMessage;
	

	
	public function index() {
		$this->load->model('checkout/order');
		$this->language->load('extension/payment/unipayment');
		$data['button_confirm'] = $this->language->get('button_confirm');
		$data['action'] = HTTP_SERVER.'index.php?route=extension/payment/unipayment/doPayment';
		$data['unipayment_environment'] = $this->config->get('payment_unipayment_environment');
		$data['text_test'] = $this->language->get('text_test');
		
		$this->load->model('checkout/order');		
		$order_info = $this->model_checkout_order->getOrder($this->session->data['order_id']);						

		if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/extension/payment/unipayment')) {
			
			return $this->load->view($this->config->get('config_template') . '/extension/payment/unipayment', $data);
		} else {		
			return $this->load->view('/extension/payment/unipayment', $data);
		}
		
	}

	
		
	public function callback() {
		$this->language->load('extension/payment/unipayment');
		
		$notify_json = file_get_contents('php://input');
		$notify_ar = json_decode($notify_json, true);
		$order_id = $notify_ar['order_id'];
		$invoice_id = $notify_ar['invoice_id'];
				
		
		$this->load->model('checkout/order');
		$this->load->model('setting/setting');		
		$order_info = $this->model_checkout_order->getOrder($order_id);	
		
		
		if (empty($order_id)) {
			$this->display_error_page(2,'Order No Not Found');						
			return ;
		}
				
		$uniPaymentClient = new \UniPayment\Client\UniPaymentClient();
		$uniPaymentClient->getConfig()->setDebug(false);
		$environment = $this->config->get('payment_unipayment_environment');
		$environment = (isset($environment)) ? $environment : 'test';		
		$uniPaymentClient->getConfig()->setIsSandbox($environment == 'test');			
		$uniPaymentClient->getConfig()->setClientId($this->config->get('payment_unipayment_client_id'));
		$uniPaymentClient->getConfig()->setClientSecret($this->config->get('payment_unipayment_client_secret'));		
		$response = $uniPaymentClient->checkIpn($notify_json);
		$paidstatus = '2';
		$failstatus = '10';
		
		
		if ($response['code'] == 'OK') {
			$error_status = $notify_ar['error_status'];
			$status = $notify_ar['status'];
			$processing_status = $this->config->get('payment_unipayment_processing_status');
		switch ($status) {
			case 'New':
				{					
					break;
				}
			case 'Paid': 				
				{
					
					$info_string  = 'Invoice : '.$invoice_id.' transaction detected on blockchain';
					error_log("    [Info] $info_string");																
					if($processing_status == $status) $this->update_payment($order_id, $paidstatus, $invoice_id, $info_string);					
					break;
				}
                    
			case 'Confirmed':
				{
					
					$info_string  = 'Invoice : '.$invoice_id.' has changed to confirmed';
					error_log("    [Info] $info_string");															
					if($processing_status == $status) $this->update_payment($order_id, $paidstatus, $invoice_id, $info_string);		
					break;
				}
			case 'Complete':
				{
					
					
					$info_string  = 'Invoice : '.$invoice_id.' has changed to complete';
					error_log("    [Info] $info_string");										
					if($processing_status == $status) $this->update_payment($order_id,$paidstatus, $invoice_id, $info_string);
					break;	
				}
				
                    
			case 'Invalid':
				{
					$error_string  = 'Invoice : '.$invoice_id.' has changed to invalid because of network congestion, please check the dashboard';
					error_log("    [Warning] $error_string");					
					break;				
				}
			case 'Expired':
				{
					$error_string  = 'Invoice : '.$invoice_id.' has chnaged to expired';
					error_log("    [Warning] $error_string");	
					
					if ($this->config->get('payment_unipayment_handle_expired_status') == 1) {
						
						$this->update_payment($order_id,$failstatus, $invoice_id, $error_string);
					}					
					break;                    
				}
			default:
				{
					error_log('    [Info] IPN response is an unknown message type. See error message below:');
					$error_string = 'Unhandled invoice status: ' . $payment_status;
					error_log("    [Warning] $error_string");
                }
		}
			
			echo "SUCCESS";			
		}
		else {
			echo "Fail";			
		}
		
		exit;
		
		
	}
	
	
	public function doPayment() {
		$this->language->load('extension/payment/unipayment');
        $this->load->model('setting/setting');
		$this->load->model('checkout/order');
		$order_id = $this->session->data['order_id'];
		$order_info = $this->model_checkout_order->getOrder($order_id);				
		$comment = 'new';
		
		$this->model_checkout_order->addOrderHistory($order_id, '1', $comment);
		
		
		$returnURL = HTTP_SERVER.'index.php?route=checkout/success&';
		$notifyURL = HTTP_SERVER.'index.php?route=extension/payment/unipayment/callback&action=notify&orderid='.$order_id;
		
		$pay_currency = $this->config->get('payment_unipayment_pay_currency');
		$desc = 'Order No : ' . $order_id;
		$langCode = $order_info['language_code'];
		$langCode = str_replace('_', '-', $langCode);
		list($lang1, $lang2) = explode('-', $langCode);		
		$langCode = strtolower($lang1).'-'. strtoupper($lang2);
		if ($lang1 = 'en') $langCode = 'en-US';
		
		
		$uniPaymentClient = new \UniPayment\Client\UniPaymentClient();
		$uniPaymentClient->getConfig()->setDebug(false);
		$environment = $this->config->get('payment_unipayment_environment');
		$environment = (isset($environment)) ? $environment : 'test';
		
		
		$uniPaymentClient->getConfig()->setIsSandbox($environment == 'test');		
		
		
		$uniPaymentClient->getConfig()->setClientId($this->config->get('payment_unipayment_client_id'));
		$uniPaymentClient->getConfig()->setClientSecret($this->config->get('payment_unipayment_client_secret'));		
		$createInvoiceRequest = new \UniPayment\Client\Model\CreateInvoiceRequest();
		$createInvoiceRequest->setAppId($this->config->get('payment_unipayment_app_id'));
		$createInvoiceRequest->setPriceAmount(round($order_info['total'], 2));
		$createInvoiceRequest->setPriceCurrency($order_info['currency_code']);
    	if ($pay_currency!= '-') $createInvoiceRequest->setPayCurrency($pay_currency);			
		$createInvoiceRequest->setOrderId($order_id);
		$createInvoiceRequest->setConfirmSpeed($this->config->get('payment_unipayment_confirm_speed'));
		$createInvoiceRequest->setRedirectUrl($returnURL);
		$createInvoiceRequest->setNotifyUrl($notifyURL);
		$createInvoiceRequest->setTitle($desc);
		$createInvoiceRequest->setDescription($desc);
		$createInvoiceRequest->setLang($langCode);
		$response = $uniPaymentClient->createInvoice($createInvoiceRequest);
		
		
		
		if ($response['code'] == 'OK') {
			$payurl = $response->getData()->getInvoiceUrl();                
			$this->response->redirect($payurl);			
		} else {			
			$errmsg = $response['msg'];
			$this->display_error_page(1,$errmsg);			
		}		
		
	}
	
	
	public function display_error_page($type,$errorMessage){

		$this->language->load('extension/payment/unipayment');
		$this->load->model('setting/setting');
		$this->document->setTitle($this->language->get('heading_title'));
		
			$data['header'] = $this->load->controller('common/header');		
			$data['column_content_bottom'] = $this->load->controller('common/content_bottom');
			$data['column_content_top'] = $this->load->controller('common/content_top');
			$data['column_left'] = $this->load->controller('common/column_left');
			$data['column_right'] = $this->load->controller('common/column_right');
			$data['footer'] = $this->load->controller('common/footer');
		

		if($type == 1){
			$data['heading_title'] = $this->language->get('unipayment_error');
		}else if ($type == 2){
			$data['heading_title'] = $this->language->get('payment_refused');
		}
		$data['text_error'] = $errorMessage;
		$data['button_continue'] = $this->language->get('button_continue');
		$data['continue'] = $this->url->link('common/home');
		

		if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/extension/error/unipayment')) {
			$this->response->setOutput($this->load->view($this->config->get('config_template') . '/extension/error/unipayment', $data));
		} else {
			$this->response->setOutput($this->load->view('/extension/error/unipayment', $data));
		}
	}
	
	public function update_payment($order_id, $newstatus, $invoice_id, $comment){				
		$this->model_checkout_order->addOrderHistory($order_id, $newstatus, $comment);	
	}
	
	
}
?>