var Netotiate = Netotiate || {};
Netotiate.Reporting = {};
Netotiate.Reporting.Data = {};
Netotiate.Reporting.eventKey = 'event';
Netotiate.Reporting.Internal = {};
Netotiate.Reporting.baseUrl = '${reportBaseUrl}';
Netotiate.Reporting.GA = {};

Netotiate.Reporting.GA.reportPageView = function(page){
	if (typeof ga != "undefined")
		ga('send','pageview', { 'title' : page });
}
Netotiate.Reporting.GA.reporEvent = function(event){
	if (typeof ga != "undefined")
		ga('send','event', event, 'Action');
}

/*
 *  Public method.
 *  eventType - string, mandatory
 *  additionalData - array, key value pairs. optional (Example: ['reason', 'variable undefined exception', 'method of exception', 'userLogIn', 'userId', '123123']
 *  affiliateId - integer, mandatory
 */
Netotiate.Reporting.Internal.reportEvent = function(eventType, action,data, affiliateId, sampleGroup, customRef, categoryId, data2){
	Netotiate.Reporting.Internal.report(eventType, action, data, affiliateId, sampleGroup, customRef, categoryId, data2);
	
	if ( typeof eventType != "undefined" && eventType != "" ) {
		Netotiate.Reporting.GA.reporEvent(eventType);
		Netotiate.Reporting.GA.reportPageView(eventType + '/' + action);
	}
};

/*
 *  Public method.
 *  pageName - string, mandatory
 *  additionalData - array, key value pairs. optional (Example: ['reason', 'variable undefined exception', 'method of exception', 'userLogIn', 'userId', '123123']
 *  affiliateId - integer, mandatory
 */
Netotiate.Reporting.Internal.reportPageView = function(pageName, data, affiliateId, customRef, categoryId, data2){
	Netotiate.Reporting.Internal.report(pageName, 'View', data, affiliateId, null, customRef, categoryId, data2);
	
	//Google analytics - report page-view.
	if (typeof pageName != "undefined" && pageName != "") {
		var googleReportPattern = '/' + pageName + '/View';
		if(data && data!="undefined")	
			googleReportPattern += '/' + data;
		if(data2 && data2!="undefined")	
			googleReportPattern += '/' + data2;
		Netotiate.Reporting.GA.reportPageView(googleReportPattern);
	}
};

/*
 *  Public method.
 *  action - string - event action (the eventType is internally set to 'error')
 *  data - string - additional data, context for the error
 *  affiliateId - integer, mandatory
 */
Netotiate.Reporting.Internal.reportError = function(action, data, affiliateId, customRef, categoryId, data2){
	Netotiate.Reporting.Internal.reportEvent('Error', action,data, affiliateId, null, customRef, categoryId, data2);
};

//Do call to ReportingController and the event parameter - BLOCKING METHOD!
Netotiate.Reporting.Internal.report = function(event, action, data, affiliateId, sampleGroup, customRef, categoryId, data2){

	if(customRef == "undefined" || customRef == null){
		customRef = document.referrer;
	}
	
	if(categoryId == "undefined" || categoryId == null){
		categoryId = '';
	}
	
	if(sampleGroup == '' || sampleGroup == undefined || sampleGroup == null){
		sampleGroup = Netotiate.SampleGroup.getName(affiliateId) ? Netotiate.SampleGroup.getName(affiliateId) : '';
	}
		
	if(!data){
		data = '';
	}
	
	if(!data2){
		data2 = '';
	}
	
	var sessionId = Netotiate.Session.getId();
	var visitorId = Netotiate.Visitor.getId();
	
	var sent = false;

	var reportingUrl = Netotiate.Reporting.baseUrl + '/reporting/internal';
	var dataObj = {
		e : event,
		a : action,
		d : data,
		d2: data2,
		affiliateId : affiliateId,
		sg : sampleGroup,
		r : customRef,
		cid: categoryId,
		sid: sessionId,
		vid: visitorId
	};
	
	var originalCors = $.support.cors;
	
	$.support.cors = true;
	
	
	var reportIE = function(){
		if( window.XDomainRequest ){
		 	var xhr = new XDomainRequest();
			xhr.open('POST', reportingUrl);
			xhr.onload = function(){};
		    xhr.onprogress = function(){};
		    xhr.timeout = 5000;//5 sec timeout
		    xhr.onerror = function(){
		    	//fail silently
		    };	

		    //Transform to query-string like format
		    var param = '';
		    for (var p in dataObj) {
		    	if (dataObj.hasOwnProperty(p)) {
		    		param += encodeURIComponent(p) + "=" + encodeURIComponent(dataObj[p]) + "&";
		    	}
		    }

		    xhr.send(param);
    	}
    };
	
	var report = function(){
		$.ajax({
	 	     url: reportingUrl,
	 	     type: "POST",
	 	     cache: false,
	 	     data : dataObj,
	 	     dataType: "json",//To allow a cross domain request
	 	     crossDomain: true,
	 	     async:false,
		     success: function( data ) {}
		});
	};
	
	var xhr = new XMLHttpRequest();
	if ("withCredentials" in xhr)
		report();
	else
		reportIE();
	
	$.support.cors = originalCors; 
	
	return true;
};

/*
 * Private method.
 * Allows reporting of events by using Post_Message events
 * Mainly used by the Plugin_Library
 */
pm.bind("do_report", function(data) {
	
	if( data && data.originator != '' && data.affiliateId != '' ){
		var customRef = '';
		
		if(data.ref != "undefined" && data.ref != null && data.ref != ''){
			customRef = data.ref;
		}
		
		if(data.eventData2 == "undefined" || data.eventData2 == null){
			data.eventData2 = '';
		}
		
		Netotiate.Reporting.Internal.reportEvent(data.originator, data.action, data.eventData, data.affiliateId, data.sg, customRef, data.cid, data.eventData2);
	}
});