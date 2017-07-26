var placeholderDiv = document.getElementById("tableauViz");
var affiliateIds = [];
var publishedSheets = null;
var viz = null;
var currentUrl = $.url();

window.addEventListener('sessionchanged', function (e) {
    var affiliateIds = e.detail.customerId.split(",");
    conditionallyReloadPage(currentReport.id, affiliateIds);
    vizFilterAffiliateId(affiliateIds, removeAuthKeyFilterFromSheets);
});

$(document).ready(initTableau);

function conditionallyReloadPage(reportId, affiliateIds) {
    var report = reports.getById(reportId);
    affiliateIds = (!affiliateIds) ? $('#inputAffiliateId').val().split(",") : affiliateIds;

    if (!placeholderDiv || (report["isDisplayAllAccounts"] == false && (affiliateIds.length > 1))) {
        location.reload();
    }
}

function initTableau() {
    if (!placeholderDiv) return;

    // get the position
    var vizPosition = $( "#report-container" ).position();

    var vizHeight = currentReport.height || ($(window).height() - (vizPosition.top + 10));
    vizHeight = vizHeight.toString().replace('px','') + 'px';

    if ($("#tableauVizImage img").css( "visibility") == 'hidden') {
        showTableauViz();
    }

    window.setTimeout(function() {
        if($("#tableauVizImage img").attr('loading-status') == 'loading') {
            showTableauViz();
        }
    }, 1500);

    var options = {
        hideTabs: true,
        hideToolbar: true,
        width: "100%",
        height: vizHeight,
        onFirstInteractive: vizFirstActive
    };

    //disable affiliate selection
    $('#inputAffiliateId').prop("disabled", true);

    //disabling all commandbars
    $('#commands-toolbar').find('input, button').prop("disabled", true);

    //$('#date-selection input[name=daterange]').prop("disabled", true);
    //$('#date-selection button').prop("disabled", true);

    // attach click function to sidebar links
    $('#side-menu .fkclss-reportlink').on("click", onReportLinkClicked);

    var ticket = tickets.shift();
    var reportPath = ((typeof currentReport.path == 'object') ?
                        currentReport.path.shift() :
                        currentReport.path);

    var affiliateIds = $('#inputAffiliateId').val().split(",");
    reportUrl = reportsBaseUrl + ticket + '/' + reportPath + '?Auth_Key=' + affiliateIds +
                '&Start%20Date=' + currentUrl.data.param.query['start'] +
                '&End%20Date=' + currentUrl.data.param.query['end'];

    viz = new tableau.Viz(placeholderDiv, reportUrl, options);

    var startDate = moment(currentUrl.data.param.query['start'],"YYYY-MM-DD").format('MM/DD/YYYY');
    var endDate = moment(currentUrl.data.param.query['end'],"YYYY-MM-DD").format('MM/DD/YYYY')
    var defaultDateRange = startDate + ' - ' + endDate;

    $('input[name="daterange"]').val(defaultDateRange);
    $('#reportrange span').text(defaultDateRange);

    // when we change the date using the datepicker
    $('input[name="daterange"]').daterangepicker({'maxDate' : moment(new Date().getTime())}, dateChanged);

    // set ui defaults on page for the report being viewed
    // console.log(currentReport)
    reportChanged(currentReport.id);

    //TODO: remove this after menu more items will be added
    $('#Conversion-Uplift').show();                             //open "Conversion Uplift" item
    $('#side-menu li a.accordion-toggle').first().parent().css('pointer-events','none'); //disable click
}

function dateChanged(start, end) {
    ga('send', 'event', 'date-range', 'click', start.format('YYYY/MM/DD') + "-" + end.format('YYYY/MM/DD'));
    vizFilterDate(start.format('YYYY-MM-DD'), end.format('YYYY-MM-DD'))
}

function appendNextReport() {
    if (typeof currentReport.path == 'string' ||
        (typeof currentReport.path == 'object' && currentReport.path < 1))
        return;

    var reportPath = currentReport.path.shift();
    var ticket = tickets.shift();

    var reportUrl = reportsBaseUrl + ticket + '/' + reportPath + '?Auth_Key=' + affiliateIds +
            '&Start%20Date=' + currentUrl.data.param.query['start'] +
            '&End%20Date=' + currentUrl.data.param.query['end'];

    var divId = currentReportId + '-' + ($('.tableauPlaceholder').length + 1);

    $("#tableauViz").parent().append("<div style='width:100%' class='tableauPlaceholder' id='" + divId + "'></div>");

    // delete options.onFirstInteractive;
    var div = document.getElementById(divId);
    var vizAppended = new tableau.Viz(div, reportUrl, options);
}

function showTableauViz() {
    // if there is no tableau viz present
    if($("#report-preloader-image").length == 0) return;

    $("#report-preloader-image").remove();

    if ($("#tableauVizImage img").css( "visibility") == 'hidden') {
        $("#tableauViz").css('z-index', 1);
        $("#tableauVizImage").css('z-index',0);
        $("#tableauVizImage").hide();
    } else {
        // if preloading div is visible, hide it, and fadein fadeout
        $("#tableauVizImage").toggleClass("transparent");
        $("#tableauVizImage").one("webkitTransitionEnd otransitionend oTransitionEnd msTransitionEnd transitionend",
            function(event) {
                $("#tableauVizImage").css('z-index',0);
                $("#tableauViz").css('z-index', 1);
            }
        );
    }
}

function vizFirstActive() {
    publishedSheets = publishedSheets || viz.getWorkbook().getPublishedSheetsInfo();
    $('#inputAffiliateId').prop("disabled", false);

    $('#commands-toolbar').find('input, button').prop("disabled", false);

    removeAuthKeyFilterFromSheets();
    appendNextReport();

    setTimeout(showTableauViz, 1500);

    // vizFilterAffiliateId(affiliateIds);
    // vizFilterDate('{{ $startDate }}', '{{ $endDate }}');
}

function onReportLinkClicked(event) {
    // event.preventDefault();

    var el = $(event.target);
    var href = el.attr('href');
    var elPath = $.url(href).data.attr.path.split('/');
    var reportId = elPath[elPath.length - 1];

    var requestedReport = reports.getById(reportId);
    currentReportBasePath = currentReport['path'].substring(0, currentReport['path'].lastIndexOf('/'));
    requestedReportBasePath = requestedReport['path'].substring(0, requestedReport['path'].lastIndexOf('/'));

    ga('send', 'event', requestedReportBasePath, 'click');

    if (currentReportBasePath === requestedReportBasePath) {
        conditionallyReloadPage(reportId);
        vizChangeTabTo(reportId, reportChanged)
        return false;
    }

    var start = $('input[name="daterange"]').data('daterangepicker').startDate.format('YYYY-MM-DD');
    var end = $('input[name="daterange"]').data('daterangepicker').endDate.format('YYYY-MM-DD');

    var url = $.url(href);
    url.data.param.query['start'] = start
    url.data.param.query['end'] = end

    var redirectUrl = url.data.attr.base + url.data.attr.path + '?' + $.param(url.data.param.query);
    el.attr('href', redirectUrl);
}


function dateButtonClicked(){
    $('input[name="daterange"]').click();
}

function reportChanged(reportId) {
    // remove any active links
    $('#side-menu .fkclss-reportlink').removeClass('active');

    // set this link as active
    $('#side-menu .fkclss-reportlink-' + reportId).addClass('active');

    var report = reports.getById(reportId);

    if (report.isRealTimeOnly !== 'undefined') {
        $('#date-selection').toggle(!report.isRealTimeOnly)
        $('#refresh-data').toggle(report.isRealTimeOnly)
    }

    currentReport = reports.getById(reportId);
}

function removeAuthKeyFilterFromSheets() {
    if (viz && !viz.getInstanceId()) return;

    var workbook = viz.getWorkbook();
    var activeSheet = workbook.getActiveSheet();

    var sheetsThatDisregardAuthKey = ['Main Funnel Steps - Reference'];

    activeSheet.getWorksheets().forEach(
        function(sheet) {
            if(sheetsThatDisregardAuthKey.indexOf(sheet.getName()) == 0) {
                sheet.clearFilterAsync("Auth_Key");
            }
        }
    );
}

function vizFilterAffiliateId(ids, cb) {
    if (viz && !viz.getInstanceId()) return;

    var workbook = viz.getWorkbook();
    var activeSheet = workbook.getActiveSheet();

    var sheets = activeSheet.getWorksheets().length;

    activeSheet.getWorksheets().forEach(
        function(sheet) {
            sheet.applyFilterAsync("Auth_Key", ids, tableau.FilterUpdateType.REPLACE).then(function(){
                sheets--;
                if(sheets == 0) cb();
            });
        }
    );

    /*
    // really slow implementation because of getFiltersAsyc for each worksheet
    activeSheet.getWorksheets().forEach(
        function(sheet) {
            console.log(sheet.getName())
            sheet.getFiltersAsync().then(function(filters) {
                // console.log(filters)
                if(filters.has('Auth_Key')){
                    sheet.applyFilterAsync("Auth_Key", ids,
                        tableau.FilterUpdateType.REPLACE);
                }
            });
        }
    );
    */

    /*
    function(){
        this.applyFilterAsync("Auth_Key", ids,
            tableau.FilterUpdateType.REPLACE);
    });

    .then(
        // promise success
        function(result) {
            // console.log(result);
        },
        function(err){
            console.log(err);
        }
    );
    */
}

function vizFilterDate(start, end) {
    if (viz && !viz.getInstanceId()) return;

    workbook = viz.getWorkbook();
    activeSheet = workbook.getActiveSheet();

    // console.log(result)
    var startDate = start.split('-');
    startDate[1] = parseInt(startDate[1]) - 1;

    var endDate = end.split('-');
    endDate[1] = parseInt(endDate[1]) - 1;

    var dateRange = {
        min: new Date(Date.UTC(startDate[0], startDate[1], startDate[2])),
        max: new Date(Date.UTC(endDate[0], endDate[1], endDate[2]))
    }

    // console.log(dateRange);
    workbook.changeParameterValueAsync('Start Date', start)
    .then(function(){
        return workbook.changeParameterValueAsync('End Date', end)
    })

    /*
    // promise
    activeSheet.getWorksheets()[0]
    //.clearFilterAsync("Date Range") // Clearing a Filter
    .applyFilterAsync("Date Range", [start, end], tableau.FilterUpdateType.REPLACE)
    .then(
        // susccess
        function(result) {
            // console.log(result);
        },
        function(err){
            console.log(err);
        }
    );
    */
}

function vizChangeTabTo(reportId, cb) {
    if (viz && !viz.getInstanceId()) return;

    var report = reports.getById(reportId)

    var baseUrl = $.url(reportUrl).data.attr.base;
    var reportPath = report.path;

    // get tab name
    reportPath = baseUrl + $.url(reportPath).data.attr.path;

    var el = publishedSheets.find(function(e){
        var url = $.url(e.getUrl());
        return reportPath === (url.data.attr.base + url.data.attr.path)
    })

    var workbook = viz.getWorkbook();
    var activeSheet = workbook.getActiveSheet();

    if (el) {
        if (report.height) {
            viz.setFrameSize('100%', report.height)
        }

        workbook.activateSheetAsync(el.getName())
        .then(
            function (sheet) {
                var affiliateIds = $('#inputAffiliateId').val().split(",");
                // vizFilterAffiliateId(affiliateIds);
                cb(reportId);
                ga('send', {'hitType': 'pageview', 'page': reportPath});
            }
        );
        return true;
    }
    return false;
}

function exportReport() {
    try {
        viz.showExportCrossTabDialog(null);
    } catch(e) {

    }
}

function refreshData() {
    try {
        viz.refreshDataAsync();
    } catch (e) {

    }
}

/*
$(window).resize(function(){
    // var width = document.getElementById(div).clientWidth;
    // var height = document.getElementById(div).clientHeight;
    // viz.setFrameSize(width, height);
    var p = $( "#tableauViz" );
    var position = p.position();

    if (viz) {
        var height = ($(window).height() - position.top) - 10
        viz.setFrameSize('100%', parseInt(height, 10));
    }

    //sheet.changeSizeAsync(
    //{"behavior": "EXACTLY", "maxSize": { "height": height, "width": width }})
    //.then(viz.setFrameSize(parseInt(width, 10), parseInt(height, 10)));
});
*/

