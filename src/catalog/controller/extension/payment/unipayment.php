<?php
require_once dirname(__FILE__).'/unipayment/vendor/autoload.php';
class ControllerExtensionPaymentUnipayment extends Controller {
	private $uniPaymentClient;
	public function index() {
		$this->language->load('extension/payment/unipayment');
		$this->load->model('checkout/order');
		$order_info = $this->model_checkout_order->getOrder($this->session->data['order_id']);
		if($order_info){
			return $this->load->view('extension/payment/unipayment');
		}
		
	}
	public function createInvoice() {
		$json = array();
		$this->language->load('extension/payment/unipayment');

		$this->load->model('checkout/order');
		$order_info = $this->model_checkout_order->getOrder($this->session->data['order_id']);
		if($order_info && isset($this->request->post['payCurrency'])){
			if ($this->config->get('payment_unipayment_sandbox')) {
				$action = 'https://sandbox.unipayment.io';
			} else {
				$action = 'https://unipayment.io';
			}

			$description = '';
			foreach ($this->cart->getProducts() as $product) {
	            $description .= htmlspecialchars($product['name']) . ' - ' . htmlspecialchars($product['model']) . ' x ' . $product['quantity'];
	        }

	        $callback = $this->url->link('extension/payment/unipayment/callback&oid='.$order_info['order_id'], '', true);
	        $ipn = $this->url->link('extension/payment/unipayment/ipn&oid='.$order_info['order_id'], '', true);

			$this->uniPaymentClient = new \UniPayment\Client\UniPaymentClient();
	        $this->uniPaymentClient->getConfig()->setAppId($this->config->get('payment_unipayment_app_id'));
	        $this->uniPaymentClient->getConfig()->setApiKey($this->config->get('payment_unipayment_app_key'));
	        $this->uniPaymentClient->getConfig()->setApiHost($action);

	        $createInvoiceRequest = new  \UniPayment\Client\Model\CreateInvoiceRequest();
	        $createInvoiceRequest->setPriceAmount($order_info['total']);
	        $createInvoiceRequest->setPriceCurrency($order_info['currency_code']);
	        $createInvoiceRequest->setPayCurrency($this->request->post['payCurrency']);
	        $createInvoiceRequest->setNotifyUrl($ipn);
	        $createInvoiceRequest->setRedirectUrl($callback);
	        $createInvoiceRequest->setOrderId($order_info['order_id']);
	        $createInvoiceRequest->setTitle((string)substr($this->config->get('config_name'), 0, 40) . ' - #' . $order_info['order_id']);
	        $createInvoiceRequest->setDescription($description);
	        $createInvoiceRequest->setLang('en-US');
	        $createInvoiceRequest->setExtArgs('');
	        $createInvoiceRequest->setConfirmSpeed('low');
	        $response = $this->uniPaymentClient->createInvoice($createInvoiceRequest);
	        
	        if ($this->config->get('payment_unipayment_debug')) {
		        $this->log->write('UNIPAYMENT REQUEST :: '. json_encode($response));
		    }
		    if ($response['code'] == 'OK'){
		    	$json['redirect'] = $response->getData()->getInvoiceUrl();
		    } else {
		    	$json['redirect'] = $this->url->link('checkout/checkout', '', true);
				$this->session->data['error'] = $response['msg'];
		    }
		} else {
			$json['redirect'] = $this->url->link('checkout/checkout', '', true);
			$this->session->data['error'] = $this->language->get('error_payment');
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));	
		
	}
		
	public function callback() {

		if ($this->config->get('payment_unipayment_debug')) {
	        $this->log->write('UNIPAYMENT CALLBACK :: '. json_encode($this->request->request));
	    }
	    if(isset($this->request->get['oid'])){
	    	$order_id = $this->request->get['oid'];
	    } elseif (isset($this->request->get['amp;oid'])) {
	    	$order_id = $this->request->get['amp;oid'];
	    } elseif (isset($this->session->data['order_id'])) {
	    	$order_id = $this->session->data['order_id'];
	    } else {
	    	$order_id = 0;
	    }

	    $this->load->model('checkout/order');
		$order_info = $this->model_checkout_order->getOrder($order_id);
	    if ($order_info) {
	    	if ($this->config->get('payment_unipayment_sandbox')) {
				$action = 'https://sandbox.unipayment.io';
			} else {
				$action = 'https://unipayment.io';
			}
			$this->uniPaymentClient = new \UniPayment\Client\UniPaymentClient();
		    $this->uniPaymentClient->getConfig()->setAppId($this->config->get('payment_unipayment_app_id'));
	        $this->uniPaymentClient->getConfig()->setApiKey($this->config->get('payment_unipayment_app_key'));
	        $this->uniPaymentClient->getConfig()->setApiHost($action);
	    	$queryInvoiceRequest = new \UniPayment\Client\Model\QueryInvoiceRequest();
		    $queryInvoiceRequest->setOrderId($order_id);
		   	
		   
		    $status = 'New';
		    $invoice_id = '';
		    $response = $this->uniPaymentClient->queryInvoices($queryInvoiceRequest);
		    if ($this->config->get('payment_unipayment_debug')) {
		        $this->log->write('UNIPAYMENT QUERY REPONSE :: '. json_encode($response));
		    }
		    if ($response['code'] == 'OK'){
			   $trans = $response['data']['models'][0];
			   $status = $trans['status'];
			   $invoice_id  = $trans['invoice_id'];			   
		    }

        	switch($status) {
				case 'Paid':
					$order_status_id = $this->config->get('payment_unipayment_paid_order_status_id');
					break;
				case 'Confirmed':
					$order_status_id = $this->config->get('payment_unipayment_confirmed_order_status_id');
					break;
				case 'Complete':
					$order_status_id = $this->config->get('payment_unipayment_complete_order_status_id');
					break;
				case 'Expired':
					$order_status_id = $this->config->get('payment_unipayment_expired_order_status_id');
					break;
				case 'Invalid':
					$order_status_id = $this->config->get('payment_unipayment_invalid_order_status_id');
					break;
			}

			$this->model_checkout_order->addOrderHistory($order_id, $order_status_id);
			if($status == 'Paid' || $status == 'Confirmed' || $status == 'Complete'){
        		$this->response->redirect($this->url->link('checkout/success', '', true));
        	} else {
        		$this->response->redirect($this->url->link('checkout/failure', '', true));
        	}
	    } else {
	    	$this->response->redirect($this->url->link('checkout/failure', '', true));
	    }
	}

	public function ipn() {

		if ($this->config->get('payment_unipayment_debug')) {
	        $this->log->write('UNIPAYMENT IPN CALLBACK :: '. json_encode($this->request->request));
	    }
	    if(isset($this->request->get['oid'])){
	    	$order_id = $this->request->get['oid'];
	    } elseif (isset($this->request->get['amp;oid'])) {
	    	$order_id = $this->request->get['amp;oid'];
	    } elseif (isset($this->session->data['order_id'])) {
	    	$order_id = $this->session->data['order_id'];
	    } else {
	    	$order_id = 0;
	    }

	    $this->load->model('checkout/order');
		$order_info = $this->model_checkout_order->getOrder($order_id);
	    if ($order_info) {
	    	if ($this->config->get('payment_unipayment_sandbox')) {
				$action = 'https://sandbox.unipayment.io';
			} else {
				$action = 'https://unipayment.io';
			}
			$this->uniPaymentClient = new \UniPayment\Client\UniPaymentClient();
		    $this->uniPaymentClient->getConfig()->setAppId($this->config->get('payment_unipayment_app_id'));
	        $this->uniPaymentClient->getConfig()->setApiKey($this->config->get('payment_unipayment_app_key'));
	        $this->uniPaymentClient->getConfig()->setApiHost($action);
	    	$queryInvoiceRequest = new \UniPayment\Client\Model\QueryInvoiceRequest();
		    $queryInvoiceRequest->setOrderId($order_id);

		    $status = 'New';
		    $invoice_id = '';
		    $response = $this->uniPaymentClient->queryInvoices($queryInvoiceRequest);
		    if ($this->config->get('payment_unipayment_debug')) {
		        $this->log->write('UNIPAYMENT IPN QUERY REPONSE :: '. json_encode($response));
		    }
		    if ($response['code'] == 'OK'){
			   $trans = $response['data']['models'][0];
			   $status = $trans['status'];
			   $invoice_id  = $trans['invoice_id'];			   
		    }

        	switch($status) {
				case 'Paid':
					$order_status_id = $this->config->get('payment_unipayment_paid_order_status_id');
					break;
				case 'Confirmed':
					$order_status_id = $this->config->get('payment_unipayment_confirmed_order_status_id');
					break;
				case 'Complete':
					$order_status_id = $this->config->get('payment_unipayment_complete_order_status_id');
					break;
				case 'Expired':
					$order_status_id = $this->config->get('payment_unipayment_expired_order_status_id');
					break;
				case 'Invalid':
					$order_status_id = $this->config->get('payment_unipayment_invalid_order_status_id');
					break;
			}

			$this->model_checkout_order->addOrderHistory($order_id, $order_status_id);
	    }
	}
		
		
}
?>