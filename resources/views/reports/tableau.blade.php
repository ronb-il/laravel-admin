@extends('layouts.tableau')

@section('content')
<div class="row" style="margin-top:10px;">
    <div id="tableauViz" class="tableauPlaceholder" style='width:100%;'></div>
</div>
@include('partials.affiliates')
@endsection

@section('custom-javascript')
<script src="{{ $tableau_host_url }}/javascripts/api/tableau-2.min.js"></script>
<script type="text/javascript" src="{{ url('scripts/moment.js') }}"></script>
<script type="text/javascript" src="{{ url('scripts/daterangepicker.js') }}"></script>
<script type="text/javascript">
    $('.form-inline').hide();
    var reports = <?php echo json_encode($reports); ?>;
    var currentReportId = {{ $report_id }};
    var reportsBaseUrl = "{{ $tableau_host_url }}/trusted/";
    var tickets = <?php echo json_encode($tickets); ?>;
</script>
<script type="text/javascript" src="{{ url('scripts/reports.js') }}"></script>
@endsection
