@extends('layouts.admin')

@section('sidebar-content')
    @foreach($reports as $key => $report)
         @if(isset($report['sub']))
                <li>
                    <a class="accordion-toggle collapsed" data-toggle="" data-target="#{{$report['title']}}" style="content:"\e114">{{$report['title']}}

                    </a>
                </li>
                <ul class="nav nav-list collapse" id="{{$report['title']}}" style="padding-left :13px">
                     @foreach($report['sub'] as $subKey => $subReport)

                        <li><a class="fkclss-reportlink fkclss-reportlink-{{ $key }}" href="{{ url("/reports/$key") }}?{{ Request::getQueryString() }}">
                              {{ $subReport['title'] }}</a></li> 

                     @endforeach
      
                   <!--08-09-16 : This Ab report will be deprecated soon-->
          <!--           @if($report['title'] == 'AB-Testing')
                        @if (Gate::allows('view', Resource::get('am-reports')))
                            <li><a href="{{ url("/abreport") }}?{{ Request::getQueryString() }}">AB Report</a></li>
                        @endif
                    @endif -->
                </ul>

         @else
                <li><a class="fkclss-reportlink fkclss-reportlink-{{ $key }}" href="{{ url("/reports/$key") }}?{{ Request::getQueryString() }}">{{ $report['title'] }}</a></li>
         @endif
    @endforeach

@endsection

@section('content')
<div class="row">
    <div class="">
    <div id="date-selection" class="input-group pull-left" style="width:240px">
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
<div class="row" style="margin-top:10px;">
    <div id="tableauViz" class="tableauPlaceholder" style='width:100%;'></div>
</div>
@endsection

@section('custom-javascript')
<script src="{{ $tableau_host_url }}/javascripts/api/tableau-2.min.js"></script>
<script type="text/javascript" src="{{ url('scripts/moment.js') }}"></script>
<script type="text/javascript" src="{{ url('scripts/daterangepicker.js') }}"></script>
<script type="text/javascript">
    var reports = <?php echo json_encode($reports); ?>;
    var currentReportId = {{ $report_id }};
    var reportsBaseUrl = "{{ $tableau_host_url }}/trusted/";
    var tickets = <?php echo json_encode($tickets); ?>;
</script>
<script type="text/javascript" src="{{ url('scripts/reports.js') }}"></script>
@endsection
