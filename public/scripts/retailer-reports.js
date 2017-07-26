var Netotiate = Netotiate || {};
Netotiate.Retailer = Netotiate.Retailer || {};

Netotiate.Retailer.Reports = {};
Netotiate.Retailer.Reports.UI = {};
Netotiate.Retailer.Reports.startDate = null;
Netotiate.Retailer.Reports.endDate = null;
Netotiate.Retailer.Reports.reportDatesMap = [];
/*
 * Private
 * 
 * Tooltip support
 */
Netotiate.Retailer.Reports.UI.showTooltip = function(x, y, contents, xaxesIdx){

    $('<div id="tooltip">' + contents + '<div style="text-align:center;"><b>' + Netotiate.Retailer.Reports.reportDatesMap[xaxesIdx] + '</b></div>' + '</div>').css( {
        position: 'absolute',
        display: 'none',
        top: y + 5,
        left: x + 15,
        border: '1px solid #fdd',
        padding: '2px',
        'background-color': '#fee',
        opacity: 0.70
    }).appendTo("body").fadeIn(200);
};

/*
 * Private
 */
Netotiate.Retailer.Reports.buildDaysXaxes = function(startDate, endDate){
	var xaxesData = [];
	var loopDate = new Date();
	
	loopDate.setTime(startDate.valueOf());
	var i = 0;
	while (loopDate.valueOf() < endDate.valueOf() + 86400000) {
	    xaxesData.push([i, '' + loopDate.getDate()]);
	    var day 	= ("0" + (loopDate.getDate())).slice(-2);
	    var month 	= ("0" + (loopDate.getMonth() + 1)).slice(-2);
	    Netotiate.Retailer.Reports.reportDatesMap[i] = loopDate.getFullYear() + '-' + (month) + '-' + day;
	    loopDate.setTime(loopDate.valueOf() + 86400000);//increment by day
	    i++;
	}

	return xaxesData;
};

Netotiate.Retailer.Reports.drawTotalRevenueTransaction = function(container, rawJson){
	var parsedTrxData = $.parseJSON( rawJson );
	var xaxesData = Netotiate.Retailer.Reports.buildDaysXaxes(new Date(parsedTrxData.fromDate), new Date(parsedTrxData.toDate));
	var trxData = parsedTrxData.rows;
	
	var revenueBars = [];
	
	for (var i = 0; i < trxData.length; i += 1){
		var totalRevenue = 	parseInt(trxData[i].totalRevenue);
		var aggDate = trxData[i].aggDate;

		for(var j =0; j < Netotiate.Retailer.Reports.reportDatesMap.length; j++){
			if(Netotiate.Retailer.Reports.reportDatesMap[j] == aggDate){
				revenueBars.push([j, totalRevenue]);
				break;
			}
		}
    }
	
	var data = [{ 	data: revenueBars, 
		label: "Revenue", 
		color: "rgba(50, 150, 150, 0.3 )"
	}];

	$.plot(container, data, {
        series: {
            bars : { show: true ,barWidth: 0.7 , align: "center"}
        },
        legend: { position: "nw", show: true, noColumns: 1 },
        xaxes: 	[ 	{ min: xaxesData[0][0], max: xaxesData[xaxesData.length-1][0], ticks: xaxesData}],
        grid: 	{ hoverable: true }
    });	
	
	/*
	 * Bind on-mouse over graph points to allow tooltips
	 */
    var previousPoint = null;
    container.bind("plothover", function (event, pos, item) {
    	
        $("#x").text(pos.x.toFixed(2));
        $("#y").text(pos.y.toFixed(2));

        if (item) {
            if (previousPoint != item.dataIndex) {
                previousPoint = item.dataIndex;
                
                $("#tooltip").remove();
                
                var x = item.datapoint[0].toFixed(0),
                    y = item.datapoint[1].toFixed(0);
                
                Netotiate.Retailer.Reports.UI.showTooltip(item.pageX, item.pageY, '$' + y + ' revenue', x);
            }
        }
        else {
            $("#tooltip").remove();
            previousPoint = null;            
        }
    });
};

Netotiate.Retailer.Reports.drawAvgDiscountPerTransaction = function(container, rawJson){//averageDiscount
	var parsedTrxData = $.parseJSON( rawJson );
	var trxData = parsedTrxData.rows;
	var xaxesData = Netotiate.Retailer.Reports.buildDaysXaxes(new Date(parsedTrxData.fromDate), new Date(parsedTrxData.toDate));
	var discountsBars = [];
	
	for (var i = 0; i < trxData.length; i += 1){
		var averageDiscounts = 	parseInt(trxData[i].avgDiscount); 
		var aggDate = trxData[i].aggDate;

		for(var j =0; j < Netotiate.Retailer.Reports.reportDatesMap.length; j++){
			if(Netotiate.Retailer.Reports.reportDatesMap[j] == aggDate){
				discountsBars.push([j, averageDiscounts]);
				break;
			}
		}
    }
	
	var data = [{ 	data: discountsBars, 
		label: "Average Discount Per Transaction", 
		color: "rgba(0, 90, 0, 0.3 )"
	}];
	
	$.plot(container, data, {
        series: {
            bars : { show: true ,barWidth: 0.7 , align: "center"}
        },
        legend: { position: "nw", show: true, noColumns: 1 },
        xaxes: 	[ 	{ min: xaxesData[0][0], max: xaxesData[xaxesData.length-1][0], ticks: xaxesData}],
        grid: 	{ hoverable: true }
    });	
	
	/*
	 * Bind on-mouse over graph points to allow tooltips
	 */
    var previousPoint = null;
    container.bind("plothover", function (event, pos, item) {
    	
        $("#x").text(pos.x.toFixed(2));
        $("#y").text(pos.y.toFixed(2));

        if (item) {
            if (previousPoint != item.dataIndex) {
                previousPoint = item.dataIndex;
                
                $("#tooltip").remove();
                
                var x = item.datapoint[0].toFixed(0),
                    y = item.datapoint[1].toFixed(0);
                
                Netotiate.Retailer.Reports.UI.showTooltip(item.pageX, item.pageY, y + '% average discount', x);
            }
        }
        else {
            $("#tooltip").remove();
            previousPoint = null;            
        }
    });
};

Netotiate.Retailer.Reports.drawPurchases = function(container, rawJson){
	var parsedTrxData = $.parseJSON( rawJson );
	var trxData = parsedTrxData.rows;
	var xaxesData = Netotiate.Retailer.Reports.buildDaysXaxes(new Date(parsedTrxData.fromDate), new Date(parsedTrxData.toDate));
	var purchasesBars = [];
	
	for (var i = 0; i < trxData.length; i += 1){
		var totalPurchased = 	parseInt(trxData[i].purchased); 
		var aggDate = trxData[i].aggDate;

		for(var j =0; j < Netotiate.Retailer.Reports.reportDatesMap.length; j++){
			if(Netotiate.Retailer.Reports.reportDatesMap[j] == aggDate){
				purchasesBars.push([j, totalPurchased]);
				break;
			}
		}
    }

	var data = [{ 	data: purchasesBars, 
					label: "Purchased Transactions", 
					color: "rgba(241, 89, 39, 1)"
				}];
	
	$.plot(container, data, {
        series: {
            bars : { show: true ,barWidth: 0.7 , align: "center"}
        },
        legend: { position: "nw", show: true, noColumns: 1 },
        xaxes: 	[ 	{ min: xaxesData[0][0], max: xaxesData[xaxesData.length-1][0], ticks: xaxesData}],
        grid: 	{ hoverable: true }
    });	

	/*
	 * Bind on-mouse over graph points to allow tooltips
	 */
    var previousPoint = null;
    container.bind("plothover", function (event, pos, item) {
    	
        $("#x").text(pos.x.toFixed(2));
        $("#y").text(pos.y.toFixed(2));

        if (item) {
            if (previousPoint != item.dataIndex) {
                previousPoint = item.dataIndex;
                
                $("#tooltip").remove();
                
                var x = item.datapoint[0].toFixed(0),
                    y = item.datapoint[1].toFixed(0);
                
                Netotiate.Retailer.Reports.UI.showTooltip(item.pageX, item.pageY, y + ' purchased transactions', x);
                            //item.series.label + " of " + x + " = " + y);
            }
        }
        else {
            $("#tooltip").remove();
            previousPoint = null;            
        }
    });
};

Netotiate.Retailer.Reports.drawOffersAndTransactions = function(container, rawJson){
	var parsedTrxData = $.parseJSON( rawJson );
	var xaxesData = Netotiate.Retailer.Reports.buildDaysXaxes(new Date(parsedTrxData.fromDate), new Date(parsedTrxData.toDate));
	var trxData = parsedTrxData.rows;
	
	var declinedBars = [];
	var expiredBars = [];
	var counteredBars = [];
	var acceptedBars = [];

	for (var i = 0; i < trxData.length; i += 1){
		var totalDeclined 	= 	parseInt(trxData[i].declined); 
		var totalExpired 	= 	parseInt(trxData[i].expired);
		var totalCountered 	= 	parseInt(trxData[i].counterOffered);
		var totalAccepted 	= 	parseInt(trxData[i].accepted);
		
		var aggDate = trxData[i].aggDate;

		for(var j =0; j < Netotiate.Retailer.Reports.reportDatesMap.length; j++){
			if(Netotiate.Retailer.Reports.reportDatesMap[j] == aggDate){
				declinedBars.push([j, 	totalDeclined]);
				expiredBars.push([j, 	totalExpired]);
				counteredBars.push([j, 	totalCountered]);
				acceptedBars.push([j, 	totalAccepted]);
				break;
			}
		}		
    }

	var data = [	
	            	{ data: declinedBars, label: "Declined Transactions", 			color: "rgba(235, 34, 40, 1)"		},
	            	{ data: expiredBars, label: "Expired Transactions", 			color: "rgba(153, 153, 153, 1 )"	},
	            	{ data: counteredBars, label: "Counter Offered Transactions", 	color: "rgba(34, 153, 237, 1)"		},
	            	{ data: acceptedBars, label: "Accepted Transactions", 			color: "rgba(1, 146, 69, 1 )"		}
	            ];
	
	$.plot(container, data, {
        series: {
        	lines: {show: false},
            bars : { show: true ,barWidth: 0.7 , align: "center"},
            stack: true
        },
        legend: { position: "nw", show: true, noColumns: 1 },
        xaxes: 	[ 	{ min: xaxesData[0][0], max: xaxesData[xaxesData.length-1][0], ticks: xaxesData}],
        grid: 	{ hoverable: true }
    });	

	/*
	 * Bind on-mouse over graph points to allow tooltips
	 */
    var previousPoint = null;
    container.bind("plothover", function (event, pos, item) {
    	
        $("#x").text(pos.x.toFixed(2));
        $("#y").text(pos.y.toFixed(2));

        if (item) {
            $("#tooltip").remove();

            var x = item.datapoint[0].toFixed(0),
                y = item.datapoint[1].toFixed(0);
            
            if(trxData[item.dataIndex]){
            	Netotiate.Retailer.Reports.UI.showTooltip(item.pageX, item.pageY, item.series.label + ' : ' + (y) + ' out of : ' + trxData[item.dataIndex].totalTransactions, x);	
            }
                        
        }
        else {
            $("#tooltip").remove();
            previousPoint = null;            
        }
    });
};
