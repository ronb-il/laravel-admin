var Netotiate = Netotiate || {};
Netotiate.URL = {};
Netotiate.URL.currentUrl = window.location.href;
Netotiate.URL.Bitly = {};
Netotiate.Utils = {};
Netotiate.Currency = {};
Netotiate.Setup = {};
Netotiate.Setup.affiliateId = 1;

Netotiate.URL.Bitly.shortenUrl = function(longUrl, response_func){
	var login = "netotiate";
	var api_key = "R_c2977ae4687d427da61c1d84100018cf";
	var shortUrl = null;

	$.ajax({
		url : "http://api.bit.ly/v3/shorten",
		data : {
			longUrl : longUrl,
			apiKey : api_key,
			login : login
		},
		dataType : "jsonp",
		async : false,
		success : function(response) {
			
			if (response != null && response.data != null && response.data.url != null) {
				response_func(response.data.url);
			}else{
				response_func(longUrl);
			}
		}
	});
};

/*
 * Capitalize the first letter of a string
 */
Netotiate.Utils.capitalizeFirstLetter = function capitaliseFirstLetter(str){
	if(str){
		return str.charAt(0).toUpperCase() + str.slice(1);	
	}else{
		return '';
	}
};

Netotiate.Utils.removeOverlay = function(){
	$('body').find('#netoiate-overlay-doc').fadeOut('slow', function(){
		$('body').find('#netoiate-overlay-doc').remove();
	});
};

Netotiate.Utils.scrollToTop = function(){
	$(window).bind('mousewheel DOMMouseScroll', function(e) {
		e.preventDefault();
	});
	
	$("html, body").animate({ scrollTop: 0 }, 800, 
			function() {
		    	// Animation complete.
				$(window).unbind('mousewheel DOMMouseScroll');
			});
};

Netotiate.Utils.uniqueId = function(prefix, more_entropy) {
	  // + original by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
	  // + revised by: Kankrelune (http://www.webfaktory.info/)
	  // % note 1: Uses an internal counter (in php_js global) to avoid
		// collision
	  // * example 1: uniqid();
	  // * returns 1: 'a30285b160c14'
	  // * example 2: uniqid('foo');
	  // * returns 2: 'fooa30285b1cd361'
	  // * example 3: uniqid('bar', true);
	  // * returns 3: 'bara20285b23dfd1.31879087'
	  if (typeof prefix === 'undefined') {
		  prefix = "";
	  }

	  var retId;
	  var formatSeed = function (seed, reqWidth) {
	    seed = parseInt(seed, 10).toString(16); // to hex str
	    if (reqWidth < seed.length) { // so long we split
	    	return seed.slice(seed.length - reqWidth);
	    }
	    if (reqWidth > seed.length) { // so short we pad
	    	return Array(1 + (reqWidth - seed.length)).join('0') + seed;
	    }
	    return seed;
	  };

	  // BEGIN REDUNDANT
	  if (!this.php_js) {
		  this.php_js = {};
	  }
	  // END REDUNDANT
	  if (!this.php_js.uniqidSeed) { // init seed with big random int
		  this.php_js.uniqidSeed = Math.floor(Math.random() * 0x75bcd15);
	  }
	  this.php_js.uniqidSeed++;

	  retId = prefix; // start with prefix, add current milliseconds hex
						// string
	  retId += formatSeed(parseInt(new Date().getTime() / 1000, 10), 8);
	  retId += formatSeed(this.php_js.uniqidSeed, 5); // add seed hex string
	  if (more_entropy) {
	    // for more entropy we add a float lower to 10
		  retId += (Math.random() * 10).toFixed(8).toString();
	  }

	  return retId;
};

Netotiate.Utils.doOverlay = function() {
	var isOverlayed = $('body').find('#netoiate-overlay-doc').length > 0;//Prevent multiple overlays
	var body = $('body');
	var overlay = $('<div id="netoiate-overlay-doc"></div>');

	if( !isOverlayed ){
		body.append(overlay);
		overlay.css({
			'width' : $(document).width() + 'px',
			'height' : $(document).height() + 'px'
		});
		overlay.hide();
		
		overlay.css({
			'position' : 'absolute',
			'top' : '0',
			'left' : '0',
			'z-index' : '99998',
			'background' : 'white',
			'opacity' : '0.8',
			'filter' : 'alpha(opacity=80)'
		}).fadeIn('slow');

		overlay.click(function() {//Overlay is not modal
			$('body').find('#netoiate-overlay-doc').fadeOut('slow', function(){
				$('body').find('#netoiate-overlay-doc').remove();
			})
		});	
	}
};


/****
 * @public
 * 
 * input
 * @price The raw decimal price to format		
 * 
 * Returns the formatted price. example: input 100 will output 100.00. input 2321 will output 2,321.00 
 ****/
Netotiate.Currency.formatNumber = function(price, locale){
	function addCommas(nStr){
		try{			
			nStr += '';
			var x = nStr.split('.');
			var x1 = x[0];
			var x2 = x.length > 1 ? '.' + x[1] : '';
			var rgx = /(\d+)(\d{3})/;
			
			while (rgx.test(x1)) {
				x1 = x1.replace(rgx, '$1' + ',' + '$2');
			}
			
			return x1 + x2;
			
		}catch(err){
			return nStr;
		}
	}
	
	if( isNaN( parseFloat( price ) ) ){
		return '0.00';
	}
	    	
    return addCommas(parseFloat(price).toFixed(2));
};

/****
 * @public
 * 
 * input
 * @number The raw decimal number to format		
 * 
 * Returns the formatted percent. example: input 100 will output 100.00%. input 2321 will output 2,321.00% 
 ****/
Netotiate.Currency.formatPercent = function(percent){
	if(percent > 100){
		percent = 100;
	}else if(percent < 0){
		percent = 0;
	}
	
    return Netotiate.Currency.formatNumber(percent) + '%';
};

Netotiate.Utils.stripHTMLString = function(str){
	var typeOfStr = typeof str;
	
	if(typeOfStr.toLowerCase() != 'string'){
		return str;
	}
	
	return str.replace(/<\/?[^>]+>/gi, '');
};

Netotiate.Utils.isEmailValid = function(email){
	var pattern = /^((([a-z]|\d|[!#\$%&'\*\+\-\/=\?\^_`{\|}~]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+(\.([a-z]|\d|[!#\$%&'\*\+\-\/=\?\^_`{\|}~]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+)*)|((\x22)((((\x20|\x09)*(\x0d\x0a))?(\x20|\x09)+)?(([\x01-\x08\x0b\x0c\x0e-\x1f\x7f]|\x21|[\x23-\x5b]|[\x5d-\x7e]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(\\([\x01-\x09\x0b\x0c\x0d-\x7f]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]))))*(((\x20|\x09)*(\x0d\x0a))?(\x20|\x09)+)?(\x22)))@((([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.)+(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.?$/i;
	if(email != undefined && email.length !== 0 && !pattern.test(email)){
		return false;
	}
	return true;
};

/*
 * Plugin Validator class - static
 */
Netotiate.Plugin = Netotiate.Plugin || {}; 
Netotiate.Plugin.Validator = Netotiate.Plugin.Validator || {}; 
Netotiate.Plugin.Validator.isUrl = function(url){
	if(url === undefined || url === null || url === ''){
		return false;
	}else{
		return url.match(/^((ht|f)tps?:)?\/\/[a-z0-9-\.]+\.[a-z]{2,4}\/?([^\s<>\#%"\,\{\}\\|\\\^\[\]`]+)?$/) !== null;	
	}
};
Netotiate.Plugin.Validator.isBoolean = function(booleanVar){
	return (((booleanVar !== undefined) && (booleanVar !== null)) && (booleanVar == 0 || booleanVar == 1));
};
Netotiate.Plugin.Validator.isNonEmptyString = function(str){
	return ((typeof str == "string") && (str.length > 0));
};

Netotiate.Utils.shortenText = function(str, maxLength){
	var typeOfStr = typeof str;
	
	if(typeOfStr.toLowerCase() != 'string'){
		return str;
	}
	
	var trimmed = $.trim(str);
	
	if(trimmed.length > maxLength){
		trimmed = trimmed.substring(0, maxLength).split(" ").slice(0, -1).join(" ") + "...";	
	}
	
	return trimmed;
};

/****
 * @public
 * 
 * input
 * @price The raw decimal price to format		
 * 
 * Returns the formatted price. example: input 100 will output 100.00. input 2321 will output 2,321.00 
 ****/
Netotiate.Currency.formatCurrency = function(price, locale){
	var defaultLocale = 'en-US';
	
	if(locale == "undefined" || locale == null){
		locale = defaultLocale;
	}
	
	if(isNaN(price) || price === ''){
		return '';
	}
    return '$' + Netotiate.Currency.formatNumber(price, locale);
};

/*
 * Allows pre-loading of images in the DOM
 */
Netotiate.Utils.preload = function preload(arrayOfImages) {
    $(arrayOfImages).each(function(){
        $('<img/>')[0].src = this;
        // Alternatively you could use:
        // (new Image()).src = this;
    });
};

/****
 * @private
 * 
 * input
 * @url The original URL to manipulate.		
 * @key The key to add/update in the queststring of the given @url.
 * @value The value to add/update to the given @key in the given @url 
 * 
 * Returns the updated URL, with the key=value embedded in the quest string. 
 ****/
Netotiate.URL.setQuerystringParam = function(url, key, value){
	return $.url.replaceQueryString(url, key, value);
};

/****
 *  @private
 *  
 *  Helper function to decode a url and switching + signs to spaces
 *  input
 *  @str to decode
 *  
 *  Returns the decoded string
 ****/
Netotiate.URL.decode = function(str) {
    return unescape(str.replace(/\+/g, " "));
};

/****
 *  @private
 * 
 *  Helper function redirect the main window
 * 
 *  input
 *  @str target URL
 *  
 *  Redirect the main browser window
 ****/
Netotiate.URL.redirect = function(targetUrl){
	window.location.replace(targetUrl);
};

/*
 * 
 */
Netotiate.Utils.initPlaceHolder = function(parent){
	var inputs
		,textareas;
	
	if(typeof parent === "undefined"){
		inputs = $('input.placeholder-container');
		textareas = $('textarea.placeholder');
	} else{
		inputs = $('input.placeholder-container',parent);
		textareas = $('textarea.placeholder',parent);
	}
	
	inputs.each(function(){
		if ($(this).val() != '') {
			$(this).siblings('.placeholder').hide();
		}
	});
	
	inputs.focus(function(){
		$(this).siblings('.placeholder').hide();
	});
	inputs.blur(function(){
		if ($(this).val() === '') {
			$(this).siblings('.placeholder').show();
		}
	});
	inputs.focusout(function(){
		if ($(this).val() === '') {
			$(this).siblings('.placeholder').show();
		}
	});
	
	textareas.each(function(){
		$(this).data('initialValue',$(this).val());
		
		if(!$(this).hasClass('no-delete')){
			$(this).focus(function(){
				if($(this).val() === $(this).data('initialValue')){
					$(this).attr('value','');
				}
			});
		}

		$(this).blur(function(){
			if($(this).val() === ''){
				$(this).attr('value',$(this).data('initialValue'));
			}
		});			
	});
};	

/*
 * 
 */
Netotiate.Utils.initLoginForm = function (){
	
	function focusElementTimeout(element){
		setTimeout(function() { element.focus(); }, 50);
	}
	
	var holder = $('#login-form-holder');
	
	if(holder.length){
		holder.find('a.form-toggle').click(function(){
			holder.toggleClass('show-forgot-pass');
			
			if($('#fld-email').is(":visible")){
				focusElementTimeout($('#fld-email').filter(':input'));
			}else if($('#fld-name').is(":visible")){
				focusElementTimeout($('#fld-name').filter(':input'));
			}else if($('#retailer-login-fld-name').is(":visible")){
				focusElementTimeout($('#retailer-login-fld-name'));
			}else if($('#checkout-email')){
				focusElementTimeout($('#checkout-email').filter(':input'));
			}else if($('#checkout-login-fld-name')){
				focusElementTimeout($('#checkout-login-fld-name').filter(':input'));
			}
			
			return false;
		});
	}
};

/*
 * Init the click events for accordion elements
 */
Netotiate.Utils.initAccordionActionsById = function(elementId){
	var table = $('#' + elementId);
	if(table.length){
		var expandAll = $('.open-offer-table')
			,closeAll = $('.close-offer-table')
			,offers = table.find('.offer')
			,expandTriggers = table.find('.offer .heading');
		
		expandAll.click(function(e){
			e.preventDefault();
			
			offers.addClass('open-state');
		});
		
		closeAll.click(function(e){
			e.preventDefault();
			
			offers.removeClass('open-state');
		});
		
		expandTriggers.click(function(e){
			e.preventDefault();
			$(this).closest('.offer').toggleClass('open-state');
		});
	}
};

Netotiate.Utils.DelayedExecution = {};
Netotiate.Utils.DelayedExecution.abortPendingDelays = function(){
	clearTimeout(Netotiate.Utils.DelayedExecution.handler);
};
Netotiate.Utils.DelayedExecution.handler = null;

Netotiate.Utils.DelayedExecution.delay = function(callback, delayTimeInMiliSeconds){
	//wait X seconds before continuing
	Netotiate.Utils.DelayedExecution.handler = setTimeout(callback, delayTimeInMiliSeconds);
};

/* http://www.overset.com/2008/04/11/mark-gibsons-json-jquery-updated/ */
(function ($) {
    m = {
            '\b': '\\b',
            '\t': '\\t',
            '\n': '\\n',
            '\f': '\\f',
            '\r': '\\r',
            '"' : '\\"',
            '\\': '\\\\'
	},
	$.toJSON = function (value, whitelist) {
		var a,          // The array holding the partial texts.
			i,          // The loop counter.
			k,          // The member key.
			l,          // Length.
			r = /["\\\x00-\x1f\x7f-\x9f]/g,
			v;          // The member value.

		switch (typeof value) {
		case 'string':
			return r.test(value) ?
				'"' + value.replace(r, function (a) {
					var c = m[a];
					if (c) {
						return c;
					}
					c = a.charCodeAt();
					return '\\u00' + Math.floor(c / 16).toString(16) + (c % 16).toString(16);
				}) + '"' :
				'"' + value + '"';

		case 'number':
			return isFinite(value) ? String(value) : 'null';

		case 'boolean':
		case 'null':
			return String(value);

		case 'object':
			if (!value) {
				return 'null';
			}
			if (typeof value.toJSON === 'function') {
				return $.toJSON(value.toJSON());
			}
			a = [];
			if (typeof value.length === 'number' &&
					!(value.propertyIsEnumerable('length'))) {
				l = value.length;
				for (i = 0; i < l; i += 1) {
					a.push($.toJSON(value[i], whitelist) || 'null');
				}
				return '[' + a.join(',') + ']';
			}
			if (whitelist) {
				l = whitelist.length;
				for (i = 0; i < l; i += 1) {
					k = whitelist[i];
					if (typeof k === 'string') {
						v = $.toJSON(value[k], whitelist);
						if (v) {
							a.push($.toJSON(k) + ':' + v);
						}
					}
				}
			} else {
				for (k in value) {
					if (typeof k === 'string') {
						v = $.toJSON(value[k], whitelist);
						if (v) {
							a.push($.toJSON(k) + ':' + v);
						}
					}
				}
			}
			return '{' + a.join(',') + '}';
		}
	};
	
})(jQuery);

