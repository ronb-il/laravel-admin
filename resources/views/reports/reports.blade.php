@extends('layouts.admin')

@section('custom-styles')
<link href="{{ url('styles/daterangepicker.css') }}" rel="stylesheet" type="text/css" media="all"  />
<link href="{{ url('styles/reports.css') }}" rel="stylesheet" type="text/css" media="all"  />
@endsection

@section('sidebar-content')
    @include('reports.sidebar')
@endsection

@section('content')
<div class="row">
    <div class="" id="commands-toolbar">
        <div id="date-selection" class="input-group pull-left">
            <input type="text" name="daterange" class="form-control" id="selected-daterange" placeholder="Date">
            <span class="input-group-btn">
                <button onclick="dateButtonClicked()" class="btn btn-default" type="button">
                    <i class="glyphicon glyphicon-calendar fa fa-calendar"></i>
                </button>
            </span>
        </div>
        <div class="btn-toolbar pull-right">
        <button id="refresh-data" class="btn btn-default" onclick="refreshData()">Refresh</button>
        <button class="btn btn-primary" onclick="viz.showExportPDFDialog()">Export as PDF</button>
        <button class="btn btn-primary" onclick="exportReport()">Export Report Data</button>
      </div>
    </div>
</div>
<div id="report-container" class="row">
    <div id="tableauVizImage">
        <img onerror="this.parentNode.style.display='none';this.style.visibility='hidden';this.setAttribute('loading-status', 'error');" onload="this.setAttribute('loading-status', 'loaded')" src="{{ $report_cache_path }}" loading-status="loading" />
    </div>
    <div id="tableauViz" class="tableauPlaceholder"></div>
</div>
<div id="report-preloader-image"><img src="{{ url("/images/ajax-loader.gif") }}" height="32" width="32" /></div>
@endsection

@section('custom-javascript')
<script type="text/javascript">
    var reports = function(reports) {
        var reports = <?php echo json_encode($reports); ?>;
        return {
            getById: function(id) {
                var ids = id.split('-');
                // if we will have more nesting we can edit this
                return iterate(reports,id);
            }
        }
    }();
    function iterate (obj, key) {
        var result;

        for (var property in obj) {
            if (obj.hasOwnProperty(property)) {
                // in case it is an object
                if (property === key) {
                    return obj[key]; // returns the value
                }
                else if (typeof obj[property] === "object") {
                    result = iterate(obj[property], key);

                    if (typeof result !== "undefined") {
                        return result;
                    }
                }

            }
        }
    }
    var currentReport = reports.getById('{{ $report_id }}');
    var reportsBaseUrl = "{{ $tableau_host_url }}/trusted/";
    var tickets = <?php echo json_encode($tickets); ?>;

    (function(){
        var vizPosition = $( "#report-container" ).position();
        var vizHeight = currentReport.height || ($(window).height() - (vizPosition.top + 10));
        vizHeight = vizHeight.toString().replace('px','');
        $("#tableauVizImage img").height(vizHeight - ((vizHeight > 2000) ? 64 : 10)); // 60 for longer images
    })();
</script>
<script src="{{ $tableau_host_url }}/javascripts/api/tableau-2.min.js"></script>
<script type="text/javascript" src="{{ url('scripts/moment.js') }}"></script>
<script type="text/javascript" src="{{ url('scripts/daterangepicker.js') }}"></script>
<script type="text/javascript" src="{{ url('scripts/reports.js') }}"></script>
@endsection
