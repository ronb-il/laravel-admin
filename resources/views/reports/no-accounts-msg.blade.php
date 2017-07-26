@extends('layouts.admin')

@section('custom-styles')
<link href="{{ url('styles/daterangepicker.css') }}" rel="stylesheet" type="text/css" media="all"  />
<link href="{{ url('styles/reports.css') }}" rel="stylesheet" type="text/css" media="all"  />
@endsection

@section('sidebar-content')
    @include('reports.sidebar')
@endsection

@section('content')
<div class="container-fluid">
    <div class='row'>
        <div class='col-md-8 col-sm-10'>
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title">No account selected</h3>
                </div>
                <div class="panel-body">Please select account from the accounts menu
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('custom-javascript')
<script type="text/javascript">
    var reports = function(reports) {
        var reports = <?php echo json_encode($reports); ?>;
        return {
            getById: function(id) {
                var ids = id.split('-');
                // if we will have more nesting we can edit this
                return (ids.length > 1) ? reports[ids[0]].sub[id] : reports[id];
            }
        }
    }();

    var currentReport = reports.getById('{{ $report_id }}');
    var reportsBaseUrl = "{{ $tableau_host_url }}/trusted/";
    var tickets = <?php echo json_encode($tickets); ?>;
</script>
<script src="{{ $tableau_host_url }}/javascripts/api/tableau-2.min.js"></script>
<script type="text/javascript" src="{{ url('scripts/moment.js') }}"></script>
<script type="text/javascript" src="{{ url('scripts/daterangepicker.js') }}"></script>
<script type="text/javascript" src="{{ url('scripts/reports.js') }}"></script>
@endsection
