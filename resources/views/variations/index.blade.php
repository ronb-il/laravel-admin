@extends('layouts.admin')

@section('sidebar-content')
    @include('variations/sidebar')
@endsection

@section('content')
    @if($affiliateId !== '*')
        @include('variations/index-content')
    @else
        @include('partials/selectaffiliate')
    @endif
@endsection

@section('custom-javascript')
    @if($affiliateId !== '*')
        <script src="{{ url('scripts/variations.js') }}"></script>
        <script>
            window.addEventListener('sessionchanged', function (e) {
                location.reload();
            });

            Personali.lab.init();
            Personali.lab.setConfig(<?php echo json_encode($variationsConfig); ?>);
            Personali.lab.selectAffiliate('{{ $affiliateId }}');
        </script>
    @endif
@endsection
