@extends('layouts/admin')

@section('sidebar')

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
    window.addEventListener('sessionchanged', function (e) {
        window.location.href = '{{ url('/insites') }}';
    });
</script>
@endsection
