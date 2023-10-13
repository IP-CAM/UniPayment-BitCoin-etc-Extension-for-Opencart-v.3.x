<?php

$_['heading_title']		= 'Unipayment';
//link 
$_['text_unipayment']		= '<a href="https://www.unipayment.io/" target="_blank"><img src="view/image/payment/unipayment.png" alt="unipayment" title="unipayment"/></a>';

// Text
$_['text_payment']		= 'Payment';
$_['text_success']		= 'Your Unipayment configuration is correct';
$_['text_incomplete']	= 'Your Unipayment configuration is incomplete ';
$_['text_all_zones']	= 'All zones';
$_['text_live']	= 'Live';
$_['text_test']	= 'SandBox';


// Entry
$_['entry_geo_zone']     		= 'Geo Zone';
$_['entry_status']       		= 'Status';
$_['entry_sort_order']   		= 'Sort order';
$_['entry_method_name']   		= 'Method name';
$_['entry_environment']  		= 'Environment';
$_['entry_unipayment_status'] = 'Module status';
$_['entry_client_id']  			= 'Client ID';
$_['entry_client_secret']   			= 'Client Secret';
$_['entry_app_id']   			= 'Payment App ID';
$_['entry_confirm_speed']   			= 'Confirm Speed';
$_['entry_pay_currency']   			= 'Pay Currency';
$_['entry_processing_status']     = 'Processing Status';
$_['entry_handle_expired_status']     	= 'Handel Expired Status';


$_['entry_template_url']   		= 'Custom template URL';
$_['entry_custom_page_code']   	= 'Custom payment page code';


$_['fieldset_unipayment_module']			= 'Settings';



// Help
$_['help_geo_zone']     	= '';
$_['help_unipayment_status']   = '';
$_['help_sort_order']   	= '';
$_['help_method_name']   	= 'Name of the method displayed to the customer at payment method selection (checkout step 5)';


$_['help_environment']  	= 'Select which enviroment the plugin is connected with.';
$_['help_client_id']  	= 'Enter Client ID Given by UniPayment';
$_['help_client_secret']   	= 'Enter Client Secret Given by UniPayment';
$_['help_app_id']   	= 'Enter Payment App ID Given by UniPayment';
$_['help_confirm_speed']   	= 'This is a risk parameter for the merchant to configure how they want to fulfill orders depending on the number of block confirmations';

$_['help_pay_currency']     	= 'Select the default pay currency used by the invoice, If not set customer will select on invoice page.';
$_['help_processing_status']  = 'Which status will be considered the order is paid.';
$_['help_handle_expired_status']  = 'If set to <b>Yes</b>, the order will set to failed when the invoice has expired and has been notified by the UniPayment IPN.';

// Error
$_['error_permission']				= 'You do not have permission to modify Unipayment payment module';
$_['required_method_name']			= $_['entry_method_name'].' required';
$_['required_client_id']			= $_['entry_client_id'].' required';
$_['required_client_secret']			= $_['entry_client_secret'].' required';
$_['required_app_id']			= $_['entry_app_id'].' required';

$_['error_config']					= 'Your Unipayment configuration is wrong';


?>