<?php
{
  	"version":"1.0",
	"state":"installed",
    "public": {
        "querystring": "bento",
        "token_name": "bento_form_token",
        "handler": "bento_form_handler",
        "operation": "bento_form_operation",
		"table": "bento_form_table",
		"criteria": "bento_form_criteria",
        "ajax": "bento_form_ajax",
		"timeout": "bento_form_timeout",
		"retrieve": "bento_form_retrieve",
        "required": "bento_form_required",
        "validate": "bento_form_validate",
        "form_name": "bento_form_name",
		"type": "bento_form_type",
        "plaintext": "bento_form_plaintext",
		"autocomplete": "bento_form_autocomplete",
        "upload": "bento_form_upload",
        "debug": "bento_form_debug",
		"format": "json",
        "loading": "bento_form_loading",
		"load_class": "bento_form_loading",
		"load_image": "black",
        "onsucess": "bento_onsuccess",
        "onfail": "bento_onfail",
        "oncomplete": "oncomplete",
        "javascript_onsuccess": "bento_javascript_onsuccess",
        "javascript_onfail": "bento_javascript_onfail",
        "javascript_onsubmit": "bento_javascript_onsubmit",
        "javascript_oncomplete": "bento_javascript_oncomplete",
		"javascript_onload": "bento_javascript_onload",
		"rules":{
				"word": "^\\w+i",
				"alphabetical": "/^[a-z]+$i/",
				"alphabet": "/^[a-z]+$i/",				
				"alpha": "/^[a-z]+$i/",
				"currency": "/^\\d{1,3}([,]?\\d{3})*(\\.\\d{2})?$/",				
				"numeric": "/^(0|[1-9][0-9]*)$/i",				
				"number": "/^(0|[1-9][0-9]*)$/i",				
				"num": "/^(0|[1-9][0-9]*)$/i",				
				"alpha_numeric": "/^[a-z\\d]+$/i",
				"alphanum": "/^\\d{4}$/i.",				
				"alpha": "/^\\d{4}$/i",
				"money": "/^(\\$|\\-|\\$\\-)?\\d{1,3}([,]?\\d{3})*(\\.\\d{2})?$/",
				"date_standard": "/^([1-9]|0[1-9]|[1-2]\\d|3[0-1])(\\|\\.|-)([1-9]|0[1-9]|1[0-2])\\2\\d{4}$/",				
				"time_civilian": "/^([1-9]|0[1-9]|1[0-2]):[0-5]\\d[\\s]?(am|pm)$/i",				
				"time_military": "/^([0-1]\\d|2[0-3]):[0-5]\\d$/",				
				"phone": "/^[\\(]?\\d{3}[\\)]?[\\s|\\.|-]?\\d{3}[\\s|\\.|-]?\\d{4}$/",				
				"phone_international": "//^\\d{1,3}[\\s|\\.|-]\\d{7,20}$/",
				"postal_code": "/^([a-z]\\d[a-z])[\\s|-]?(\\d[a-z]\\d)$/i",	
				"zip_code": "^/\\d{5}(-\\d{4})?$/",				
				"username": "/^[a-z0-9_-]+$/i",	
				"http": "^((http|https|ftp):\\/\\/)?([a-z0-9_-]+)(\\.[a-z0-9_-]+)+(\\/\\w+)*(\\.[a-z0-9_-]+)*$/i",
				"email": "/^([a-z0-9_-]+)(\\.[a-z0-9_-]+)*@([a-z0-9_-]+)(\\.[a-z0-9_-]+)*[\\.]([a-z0-9_-]+)$/i"			
			},
		"password_mismatch": "Passwords do not match.",
		"timeouts":{
				"pull":10,
				"push":60
		}
    },
    "private": {
		"recaptcha":"recaptchalib",
        "private": "6LcUiMQSAAAAAH__WKoYJzGGqd6lRvHNwUBARVCF",
        "public": "6LcUiMQSAAAAANuQqz5hQCOMqq5Zck9tnjm_TBkC",
		"token_value":	"",
		"comet":{
			"iterate":1,
			"refresh":30	
		}
    }
}
?>