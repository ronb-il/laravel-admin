var errorIsAlreadyAdded = false
,param
,errorArray = {}
,validateConfig = {
	'consumer-profile-pwd-form':{
			fieldsList: {
				'profile_password_current': {
					rules: {
						required: true,
						minlength: 4,
						maxlength: 32
					},
					messages: {
						required:  'Password is required',
						minlength: 'Password length should be between 4 to 32 characters',
						maxlength: 'Password length should be between 4 to 32 characters'
					}
				},
				'profile_password_new': {
					rules: {
						required: true,
						minlength: 4,
						maxlength: 32
					},
					messages: {
						required:  'New password is required',
						minlength: 'Password length should be between 4 to 32 characters',
						maxlength: 'Password length should be between 4 to 32 characters'
					}
				},
				'profile_password_new_confirm': {
					rules: {
						required: true,
						equalTo: '#profile_password_new'
					},
					messages: {
						required: 'The passwords entered are not identical',
						equalTo:  'The passwords entered are not identical'
					}
				}
			},
			onSuccess:function(form){
				$('#' + form).submit();
				return false;
			}
},
'consumer-profile-form':{
		fieldsList: {
			'profile_first_name': {
				rules: {
					minlength: 2,
					alphanumeric:true
				},
				messages: {		
					minlength: 'Please type least 2 characters',
					alphanumeric: 'Only alphanumeric characters are allowed'
				}
			},
			'profile_last_name': {
				rules: {
					minlength: 2,
					alphanumeric:true
				},
				messages: {
					minlength: 'Please type least 2 characters',
					alphanumeric: 'Only alphanumeric characters are allowed'
				}
			},
			'profile_zip_code': {
				rules: {
					numeric: true,
					minlength: 5
				},
				messages: {
					numeric: 'Zip code can only contain digits',
					minlength: 'Zip code must be 5 digits long'
				}
			}
		},
		onSuccess:function(form){
			$('#' + form).submit();
			return false;
		}
},
'register-form':{
	fieldsList: {
		'fld-name-register': {
			rules: {
				required: true,
				minlength: 4,
				alphanumeric:true
			},
			messages: {
				required: 'Username is required',
				minlength: 'Username must be at least 4 characters long',
				alphanumeric: 'Username must contain alphanumeric characters only'
			}
		},
		'fld-pass-register': {
			rules: {
				required: true,
				minlength: 4,
				maxlength: 32
			},
			messages: {
				required: 'Password is required',
				minlength: 'Password length should be between 4 to 32 characters',
				maxlength: 'Password length should be between 4 to 32 characters'
			}
		},
		'fld-email-register': {
			rules: {
				required: true,
				email: true
			},
			messages: {
				required: 'Email address is required',
				email: 'Please enter correct email address'
			}
		},
		'fld-zip-register': {
			rules: {
				numeric: true,
				minlength: 5
			},
			messages: {
				numeric: 'Zip code can only contain digits',
				minlength: 'Zip code must be 5 digits long'
			}
		},
		'fld-re-pass-register': {
			rules: {
				required: true,
				equalTo: '#fld-pass-register'
			},
			messages: {
				required: 'The passwords entered are not identical. Please try again',
				equalTo: 'The passwords entered are not identical. Please try again'
			}
		},
		'register-terms':{
			rules: {
				required: true
			},
			messages: {
				required: 'Please agree with Netotaite\'s Terms and Conditions'
			}
		}
	},
	onSuccess:function(){
		Netotiate.Auth.Registration.register();
		return false;
	}
},
'counter-offer-discount':{
	fieldsList: {
		'counter-offer-discount-price': {
			rules: {
				required  : true,
				numeric   : true
			},
			messages: {
				required  : 'Please enter the price prior to adding the deal',
				numeric   : 'Price must be up to 8 digits long'
			}
		}
	},
	onSuccess:function(formId){
		Netotiate.Retailer.CounterOffer.selectDeal(formId);
		return false;
	}
},
'counter-offer-discount-code':{
	fieldsList: {
		'counter-offer-discount-code-checkout-code': {
			rules: {
				required  : true
			},
			messages: {
				required  : 'Please enter the checkout code prior to adding the deal'
			}
		},
		'counter-offer-discount-code-price': {
			rules: {
				required  : true,
				numeric   : true
			},
			messages: {
				required  : 'Please enter the price prior to adding the deal',
				numeric   : 'Price must be up to 8 digits long'
			}
		}
	},
	onSuccess:function(formId){
		Netotiate.Retailer.CounterOffer.selectDeal(formId);
		return false;
	}
},
'counter-offer-discount-url':{
	fieldsList: {
		'counter-offer-discount-url-price': {
			rules: {
				required  : true,
				numeric   : true
			},
			messages: {
				required  : 'Please enter the price prior to adding the deal',
				numeric   : 'Price must be up to 8 digits long'
			}
		},
		'counter-offer-discount-url-checkout-url': {
			rules: {
				required  : true,
				url		: true
			},
			messages: {
				required  : 'Please enter the checkout URL prior to adding the deal',
				url: 'Please enter a valid checkout URL before adding the deal'
			}
		}
	},
	onSuccess:function(formId){
		Netotiate.Retailer.CounterOffer.selectDeal(formId);
		return false;
	}
},
'counter-offer-freeshipping':{
	fieldsList: {
		'counter-offer-freeshipping-price': {
			rules: {
				required  : true,
				numeric   : true
			},
			messages: {
				required  : 'Please enter the price prior to adding the deal',
				numeric   : 'Price must be up to 8 digits long'
			}
		}
	},
	onSuccess:function(formId){
		Netotiate.Retailer.CounterOffer.selectDeal(formId);
		return false;
	}
},
'counter-offer-freeshipping-url':{
	fieldsList: {
		'counter-offer-freeshipping-url-price': {
			rules: {
				required  : true,
				numeric   : true
			},
			messages: {
				required  : 'Please enter the price prior to adding the deal',
				numeric   : 'Price must be up to 8 digits long'
			}
		},
		'counter-offer-freeshipping-url-checkout-url': {
			rules: {
				required  : true,
				url		: true
			},
			messages: {
				required  : 'Please enter the checkout URL prior to adding the deal',
				url: 'Please enter a valid checkout URL before adding the deal'
			}
		}
	},
	onSuccess:function(formId){
		Netotiate.Retailer.CounterOffer.selectDeal(formId);
		return false;
	}
},
'counter-offer-coupon':{
	fieldsList: {
		'counter-offer-coupon-price': {
			rules: {
				required  : true,
				numeric   : true
			},
			messages: {
				required  : 'Please enter the price prior to adding the deal',
				numeric   : 'Price must be up to 8 digits long'
			}
		},
		'counter-offer-coupon-discount': {
			rules: {
				required  : true,
				numeric   : true
			},
			messages: {
				required  : 'Please enter the coupon value prior to adding the deal',
				numeric   : 'Coupon value must be up to 6 digits long'
			}
		}
	},
	onSuccess:function(formId){
		Netotiate.Retailer.CounterOffer.selectDeal(formId);
		return false;
	}
},
'counter-offer-coupon-code':{
	fieldsList: {
		'counter-offer-coupon-code-checkout-code': {
			rules: {
				required  : true
			},
			messages: {
				required  : 'Please enter the checkout code prior to adding the deal'
			}
		},
		'counter-offer-coupon-code-price': {
			rules: {
				required  : true,
				numeric   : true
			},
			messages: {
				required  : 'Please enter the price prior to adding the deal',
				numeric   : 'Price must be up to 8 digits long'
			}
		},
		'counter-offer-coupon-code-discount': {
			rules: {
				required  : true,
				numeric   : true
			},
			messages: {
				required  : 'Please enter the coupon value prior to adding the deal',
				numeric   : 'Coupon value must be up to 6 digits long'
			}
		}
	},
	onSuccess:function(formId){
		Netotiate.Retailer.CounterOffer.selectDeal(formId);
		return false;
	}
},
'counter-offer-upgradedshipping':{
	fieldsList: {
		'counter-offer-upgradedshipping-price': {
			rules: {
				required  : true,
				numeric   : true
			},
			messages: {
				required  : 'Please enter the price prior to adding the deal',
				numeric   : 'Price must be up to 8 digits long'
			}
		},
		'counter-offer-upgradedshipping-shipping': {
			rules: {
				required  : true,
				maxlength   : 32
			},
			messages: {
				required  : 'Please enter the upgraded shipping details prior to adding the deal',
				maxlength   : 'Upgraded shipping details must be up to 32 characters'
			}
		}
	},
	onSuccess:function(formId){
		Netotiate.Retailer.CounterOffer.selectDeal(formId);
		return false;
	}
},
'counter-offer-accessory':{
	fieldsList: {
		'counter-offer-accessory-price': {
			rules: {
				required  : true,
				numeric   : true
			},
			messages: {
				required  : 'Please enter the price prior to adding the deal',
				numeric   : 'Price must be up to 8 digits long'
			}
		},
		'counter-offer-accessory-item': {
			rules: {
				required  : true
			},
			messages: {
				required  : 'Please enter the gift prior to adding the deal'
			}
		}
	},
	onSuccess:function(formId){
		Netotiate.Retailer.CounterOffer.selectDeal(formId);
		return false;
	}
},
'counter-offer-accessory-code':{
	fieldsList: {
		'counter-offer-accessory-checkout-code': {
			rules: {
				required  : true
			},
			messages: {
				required  : 'Please enter the checkout code prior to adding the deal'
			}
		},
		'counter-offer-accessory-code-price': {
			rules: {
				required  : true,
				numeric   : true
			},
			messages: {
				required  : 'Please enter the price prior to adding the deal',
				numeric   : 'Price must be up to 8 digits long'
			}
		},
		'counter-offer-accessory-code-item': {
			rules: {
				required  : true
			},
			messages: {
				required  : 'Please enter the gift prior to adding the deal'
			}
		}
	},
	onSuccess:function(formId){
		Netotiate.Retailer.CounterOffer.selectDeal(formId);
		return false;
	}
},
'retailer-reset-password-form':{
	fieldsList: {
		'fld-new-pass': {
			rules: {
				required  : true,
				minlength : 4,
				maxlength : 32
			},
			messages: {
				required  : 'Password is required',
				minlength : 'The password is too short (4 - 32 characters)',
				maxlength : 'The password is too long (4 - 32 characters)'
			}
		},
		'fld-pass-confirm': {
			rules: {
				required  : true,
				minlength : 4,
				maxlength : 32,
				equalTo	  : '#fld-new-pass'
			},
			messages: {
				equalTo	  : 'The passwords entered are not identical. Please try again',
				minlength : 'The password is too short (4 - 32 characters)',
				maxlength : 'The password is too long (4 - 32 characters)'
			}
		}
	},
	onSuccess:function(){
		Netotiate.Auth.Retailer.changePassword();
		return false;
	}
},
'na-login-on-the-spot-form-b-full-form':{
	fieldsList: {
		'na-fld-email-b' : {
			rules: {
				required: true,
				email: true
			},
			messages: {
				email: 'please_use_a_valid_email_address',
				required: 'please_use_a_valid_email_address'
			}
		},
		'phone-part-1-b': {
			rules: {
				minlength : 6
			},
			messages: {
				minlength : 'please_enter_a_valid_phone_number'
			}
		},
		'zipcode-b': {
			rules: {
				minlength : 3
			},
			messages: {
				minlength : 'please_enter_a_valid_zipcode'
			}
		}
	},
	onSuccess:function(form){
		userActivateB();
		return false;
	}
},
'na-login-on-the-spot-form-b-email-form':{
	fieldsList: {
		'na-fld-email-b' : {
			rules: {
				required: true,
				email: true
			},
			messages: {
				email: 'please_use_a_valid_email_address',
				required: 'please_use_a_valid_email_address'
			}
		}
	},
	onSuccess:function(form){
		userActivateB();
		return false;
	}
},
'na-login-on-the-spot-form-b-empty-form':{
	fieldsList: {
		'na-fld-email-b' : {
			rules: {
				required: true,
				email: true
			},
			messages: {
				email: 'please_use_a_valid_email_address', //TODO:ask Ofir
				required: 'please_use_a_valid_email_address'
			}
		}
	},
	onSuccess:function(form){
		userActivateB();
		return false;
	}
},
'na-login-on-the-spot-form':{
	fieldsList: {
		'na-fld-email' : {
			rules: {
				required: true,
				email: true
			},
			messages: {
				email: 'please_use_a_valid_email_address',
				required: 'please_use_a_valid_email_address'
			}
		},
		'phone-part-1': {
			rules: {
				minlength : 6
			},
			messages: {
				minlength : 'please_enter_a_valid_phone_number'
			}
		},
		'zipcode': {
			rules: {
				minlength : 3
			},
			messages: {
				minlength : 'please_enter_a_valid_zipcode'
			}
		}
	},
	onSuccess:function(form){
		userActivate();
		return false;
	}
},
'checkout-login-form':{
	fieldsList: {
		'checkout-login-fld-name': {
			rules: {
				required: true
			},
			messages: {
				required  : 'Username missing'
			}
		},
		'checkout-login-fld-pass': {
			rules: {
				required  : true,
				minlength : 4,
				maxlength : 32
			},
			messages: {
				required  : 'Password is required',
				minlength : 'The password is too short (4 - 32 characters required)',
				maxlength : 'The password is too long (4 - 32 characters required)'
			}
		}
	},
	onSuccess:function(){
		Netotiate.Auth.Checkout.login();
		return false;
	}
},
'checkout-password-recovery':{
	fieldsList: {
		'checkout-email': {
			rules: {
				required: true,
				email: true
			},
			messages: {
				required: 'Email address is required',
				email: 'Please enter a valid email address'
			}
		}
	},
	onSuccess:function(form){
		Netotiate.Auth.Checkout.forgotPwd();
		return false;
	}
},
'retailer-login-form':{
	fieldsList: {
		'retailer-login-fld-name': {
			rules: {
				required: true
			},
			messages: {
				required  : 'Username is missing'
			}
		},
		'retailer-login-fld-pass': {
			rules: {
				required  : true,
				minlength : 4,
				maxlength : 32
			},
			messages: {
				required  : 'Password is required',
				minlength : 'The password is too short (4 - 32 characters required)',
				maxlength : 'The password is too long (4 - 32 characters required)'
			}
		}
	},
	onSuccess:function(){
		Netotiate.Auth.Retailer.login();
		return false;
	}
},
'requestdemo-form':{
	fieldsList: {
		'name': {
			rules: {
				required: true
			},
			messages: {
				required  : 'Your name is needed'
			}
		},
		'email': {
			rules: {
				email: true,
				required: true
			},
			messages: {
				required  : 'We need your email to get back to you',
				email : 'There seems to be a type-o in the email address'
			}
		},
		'company': {
			rules: {
				required: true
			},
			messages: {
				required  : 'Company name is needed'
			}
		},
		'website': {
			rules: {
				required: true
			},
			messages: {
				required  : 'Your website address is needed'
			}
		}
	},
	onSuccess:function(){
		Netotiate.Company.requestDemo();
		return false;
	}
},
'retailer-password-recovery':{
	fieldsList: {
		'fld-email': {
			rules: {
				required: true,
				email: true
			},
			messages: {
				required: 'Email address is required',
				email: 'Please enter a valid email address'
			}
		}
	},
	onSuccess:function(form){
		Netotiate.Auth.Retailer.forgotPwd();
		return false;
	}
},
'login-form':{
	fieldsList: {
		'fld-name': {
			rules: {
				required: true,
				minlength: 4
			},
			messages: {
				required  : 'Username is required',
				minlength : 'Username must be at least 4 characters long'
			}
		},
		'fld-pass': {
			rules: {
				required  : true,
				minlength : 4,
				maxlength : 32
			},
			messages: {
				required  : 'Password is required',
				minlength : 'The password is too short (4 - 32 characters required)',
				maxlength : 'The password is too long (4 - 32 characters required)'
			}
		}
	},
	onSuccess:function(){
		Netotiate.Auth.Login.login();
		return false;
	}
},
'na-login-form':{
	fieldsList: {
		'na-fld-email': {
			rules: {
				required: true,
				email: true
			},
			messages: {
				required: 'Email address is required',
				email: 'Please enter a valid email address'
			}
		},
		'na-fld-pass': {
			rules: {
				required  : true,
				minlength : 4,
				maxlength : 32
			},
			messages: {
				required  : 'Password is required',
				minlength : 'The password is too short (4 - 32 characters required)',
				maxlength : 'The password is too long (4 - 32 characters required)'
			}
		},
		'phone-part-1': {
			rules: {
				minlength : 3,
				numeric   : true
			},
			messages: {
				minlength : 'Please enter a valid phone number (digits only)',
				numeric   : 'Please enter a valid phone number (digits only)'
			}
		},
		'phone-part-2': {
			rules: {
				minlength : 3,
				numeric   : true
			},
			messages: {
				minlength : 'Please enter a valid phone number (digits only)',
				numeric   : 'Please enter a valid phone number (digits only)'
			}
		},
		'phone-part-3': {
			rules: {
				minlength : 4,
				numeric   : true
				
			},
			messages: {
				minlength : 'Please enter a valid phone number (digits only)',
				numeric   : 'Please enter a valid phone number (digits only)'
			}
		}
	},
	onSuccess:function(form){
		netotiateArenaDoLogin();
		return false;
	}
},
'password-recovery':{
	fieldsList: {
		'fld-email': {
			rules: {
				required: true,
				email: true
			},
			messages: {
				required: 'Email address is required',
				email: 'Please enter a valid email address'
			}
		}
	},
	onSuccess:function(form){
		Netotiate.Auth.Login.forgotPwd();
		return false;
	}
},
'reset-pwd':{
	fieldsList: {
		'fld-newpass': {
			rules: {
				required: true,
				minlength : 4,
				maxlength : 32
			},
			messages: {
				required  : 'Password is required',
				minlength : 'The password is too short (4 - 32 characters required)',
				maxlength : 'The password is too long (4 - 32 characters required)'
				}
		},
		'fld-re-newpass': {
			rules: {
				required: true,
				equalTo: '#fld-newpass'
			},
			messages: {
				required: 'The passwords entered are not identical. Please try again',
				equalTo: 'The passwords entered are not identical. Please try again'
				}
			}
		},
		onSuccess:function(form){
			Netotiate.Auth.resetPassword();
			return false;
		}
	},
	'support-form':{
		fieldsList: {
			'fld-input-email' :{
				rules: {
					required: true,
					email:true
				},
				messages: {
					required: "support_Please_enter_a_valid_email",
					email: 'support_Please_enter_a_valid_email'
				}
			},
			'fld-input-regarding' :{
				rules: {
					required:true
				},
				messages: {
					required: "support_please_select_a_subject"
				}
			}
	},
	onSuccess:function(form){
		$('#' + form + ' .support-form-flds-wrapper #support-form-submit').unbind('click').click(function(){return false;});
		$.ajax({
				url: "/info/submit-support-request/",
				type: "post",
				data: $('#' + form).serialize(),
				timeout: 5000,
				beforeSend:function(){
					Netotiate.Info.SupportForm.beforeSend();
				},
				success: function(data){
					Netotiate.Info.SupportForm.success(data);
				},
				error:function(){
					Netotiate.Info.SupportForm.error();
				}
		});
		 return false;
	 }
}
};


var validationRules = {
	required:function(param){
		if(param.elem.attr('type') === 'checkbox'){
			if(!param.elem.is(':checked')){
				return false;
			}
		} else if(param.elem.val() === ''){
			return false;
		}
		return true;
	},
	minlength:function(param){
		if(param.elem.val().length !== 0 && param.elem.val().length < param.value){
			return false;
		}
		return true;
	},
	maxlength:function(param){
		if(param.elem.val().length !== 0 && param.elem.val().length > param.value){
			return false;
		}
		return true;
	},
	equalTo:function(){
		if(param.elem.val() !== $(param.value).val()){
			return false;
		}
		return true;
	},
	numeric:function(){
		var val = $.trim(param.elem.val());
		
		if( (val.length !== 0 && /^\s*?((\d+(\.\d+)?)|(\.\d+))\s*$/.test(val) ) || (val === '')){
			return true;
		}
		return false;
	},
	alphanumeric:function(){///^[0-9a-bA-B]+$/;
		var pattern = /^[a-zA-Z0-9@\s\-_]+$/;
		if(param.elem.val().length !== 0 && !pattern.test(param.elem.val())){
			return false;
		}
		
		return true;
	},
	email:function(){
		var pattern = /^((([a-z]|\d|[!#\$%&'\*\+\-\/=\?\^_`{\|}~]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+(\.([a-z]|\d|[!#\$%&'\*\+\-\/=\?\^_`{\|}~]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+)*)|((\x22)((((\x20|\x09)*(\x0d\x0a))?(\x20|\x09)+)?(([\x01-\x08\x0b\x0c\x0e-\x1f\x7f]|\x21|[\x23-\x5b]|[\x5d-\x7e]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(\\([\x01-\x09\x0b\x0c\x0d-\x7f]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]))))*(((\x20|\x09)*(\x0d\x0a))?(\x20|\x09)+)?(\x22)))@((([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.)+(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.?$/i;
		if(param.elem.val().length !== 0 && !pattern.test(param.elem.val())){
			return false;
		}
		return true;
	},
	url:function() {
		var pattern = /^(http|https):\/\/[^\s]+$/i;
		if(param.elem.val().length !== 0 && !pattern.test(param.elem.val())){
			return false;
		}
		return true;
	}
};


function bindValidateHandlers(){
	for (var form in validateConfig){
		$('#na-register-terms, #register-terms').unbind('change').change(function(e) {
			var formid = $(this).closest("form").attr('id');
			validateAll(formid);
		});

		$('#' + form + '-submit').unbind('click').click(function(form){
			var formid = $(this).closest("form").attr('id');
			
			validateAll(formid);
			
			if(errorArray[formid].length)
				$('#' + errorArray[formid][0].fieldName).focus();
					
			if (typeof validateConfig[formid].onSuccess !== "undefined" && !errorArray[formid].length) {
				//This is were we need to fire the actual submittion of the data to the server, because form passed validation.
				return validateConfig[formid].onSuccess(formid);
			}
			return false;
		});
	
		for(var field in validateConfig[form].fieldsList){
			$('#' + field).unbind('blur').blur(function(){
				
				for (var form in validateConfig) {
					for (var field in validateConfig[form].fieldsList) {
						if( ($(this).attr('id')) === field && ($(this).val() != '') ){
							for(var rule in validateConfig[form].fieldsList[field]['rules']){
								param = {
										elem:$(this),
										formName:form,
										fieldObj:validateConfig[form].fieldsList[field],
										fieldName:field,
										rule:rule,
										value:validateConfig[form].fieldsList[field]['rules'][rule]
									}
									addRemoveError(validationRules[rule](param),param);
								}
							}
						}
					}
				});
			}
	}
};

function validateAll(form){
	for (var field in validateConfig[form].fieldsList) {
		for (var rule in validateConfig[form].fieldsList[field]['rules']) {
		param = {
				elem: $('#' + field),
				formName: form,
				fieldObj: validateConfig[form].fieldsList[field],
				fieldName: field,
				rule: rule,
				value: validateConfig[form].fieldsList[field]['rules'][rule]
			}
			addRemoveError(validationRules[rule](param), param);
		}
	}
};

function addRemoveError(validationResult,param){
	errorArray[param.formName] = errorArray[param.formName] || [];

	var previousError = null;

	if(validationResult === false){
		for(var i = 0; i < errorArray[param.formName].length; i++){
			if (errorArray[param.formName][i].fieldName === param.fieldName && errorArray[param.formName][i].rule === param.rule){
				errorIsAlreadyAdded = true;
			}
		}
		if (!errorIsAlreadyAdded) {
			errorArray[param.formName].push({
				fieldName:param.fieldName,
				rule:param.rule,
				message:_t(param.fieldObj['messages'][param.rule])
			});
		}
		errorIsAlreadyAdded = false;
	} else if(validationResult === true){
		for (var i = 0; i < errorArray[param.formName].length; i++){
			if(errorArray[param.formName][i].fieldName === param.fieldName && errorArray[param.formName][i].rule === param.rule){
				previousError = errorArray[param.formName][0].fieldName;
				errorArray[param.formName].splice(i,1);
			}
		}
	}
	
	showError(param,previousError);
};

function showError(param,previousError){
	var errorHolder = $('#' + param.formName).find('.error-message');

	if(errorArray[param.formName].length){
		errorHolder.html(errorArray[param.formName][0].message);
		$('#' + errorArray[param.formName][0].fieldName).closest('div').addClass('error');
	} else{
		errorHolder.html('');
	}
	if (previousError !== null) {
		$('#' + previousError).closest('div').removeClass('error');
		}
};

function registerTermsBehavior(){
	var forms = $('#register-terms, #na-register-terms');

	$.each(forms, function() { 
			$(this).closest('form').find('.btn-row .disabled.btn').css('display', 'inline-block').show();
			$(this).closest('form').find('#registration_frm_plugin-submit, #register-form-submit').hide();
	});
	$('#register-terms, #na-register-terms').click(function(){	
		if($(this).is(':checked')){
			$(this).closest('form').find('span.disabled.btn-dark span').hide();
			$(this).closest('form').find('#registration_frm_plugin-submit, #register-form-submit').css('display', 'inline-block');
		} else{
			$(this).closest('form').find('span.disabled.btn-dark span').css('display', 'inline-block');
			$(this).closest('form').find('#registration_frm_plugin-submit, #register-form-submit').hide();
			}
		});
};