@extends('layouts.admin')

@section('sidebar-content')
    @include('behaviorrules/sidebar')
@endsection

@section('custom-styles')
    <link href="{{ url('styles/datatables.css') }}" rel="stylesheet">
    <link href="{{ url('styles/dataTables.bootstrap.css') }}" rel="stylesheet">
    <link href="{{ url('styles/daterangepicker.css') }}" rel="stylesheet" media="all" />
@endsection

@section('content')
    @if($affiliateId !== '*')
        @include('behaviorrules/logs-content')
    @else
        @include('partials/selectaffiliate')
    @endif
@endsection

@section('custom-javascript')
    <script src="{{ url('scripts/jquery.dataTables.min.js') }}" type="text/javascript"></script>
    <script src="{{ url('scripts/dataTables.bootstrap.js') }}" type="text/javascript"></script>
    <script src="{{ url('scripts/moment.js') }}"></script>
    <script src="{{ url('scripts/daterangepicker.js') }}"></script>
    <script>
        var endDate = moment().format('MM/DD/YYYY');
        var startDate = moment().subtract('10', 'days').format('MM/DD/YYYY');

        var defaultDateRange = startDate + ' - ' + endDate;

        $('input[name="daterange"]').val(defaultDateRange);

        $('input[name="daterange"]').change( function() {
            if($("#logs").attr('initialized') === "true"){
                var table = $('#logs').DataTable();
                table.draw();
            };
        });

        $('input[name="userName"], input[name="messageFilter"]').on('keyup', function(e) {
            if($("#logs").attr('initialized') === "true"){
                var table = $('#logs').DataTable();
                table.draw();
            }
        });

        // when we change the date using the datepicker
        $('input[name="daterange"]').daterangepicker({'maxDate' : moment(new Date().getTime())});

        $.ajaxSetup({
            headers: { 'X-CSRF-TOKEN': "{{ csrf_token() }}" }
        });

        $('#logs').dataTable({
            "lengthMenu": [[15, 25, 50, -1], [15, 25, 50, "All"]],
            "bFilter": false,
            "bSort": false,
            "serverSide": true,
            "dom": '<t>p<"clear">',

            "ajax": {
                url: "{{ url('logservice/find/behavior_rules') }}",
                type: 'POST',
                data: function(d) {
                    var startDate = $.trim($('input[name="daterange"]').val().split('-')[0]);
                    var endDate = $.trim($('input[name="daterange"]').val().split('-')[1]);
                    var userName = $.trim($('input[name="userName"]').val());
                    var messageFilter = $.trim($('input[name="messageFilter"]').val());
                    return $.extend(d, {
                        startDate: startDate,
                        endDate: endDate,
                        userName: userName,
                        message: messageFilter
                    });
                }
            },
            "columns": [
                { "data": "date" },
                { "data": "userName" },
                { "data": "message" }
            ],
            "columnDefs": [
                { "width": "20%", targets: 0 },
                { "width": "20%", targets: 1 },
                { "width": "60%", targets: 2 }
            ],
            "initComplete": function( settings, json ) {
                // set intialized to true as attribute in table
                $("#logs").attr('initialized', "true");

                $('#entries-per-page').on( 'click', function (e) {
                    var table = $('#logs').DataTable();
                    table.page.len( e.target.value ).draw();
                } );
            }
        });

        function dateButtonClicked(){
            $('input[name="daterange"]').click();
        }

    </script>
@endsection
