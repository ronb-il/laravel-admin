@extends('layouts.admin')

@section('sidebar-content')
    @include('smartpack/smart-pack-sidebar')
@endsection

@section('content')
    @if($affiliateId !== '*')
        @include('smartpack/smart-pack-lists-content')
    @else
        @include('partials/selectaffiliate')
    @endif
@endsection

@section('custom-javascript')
    @if($affiliateId !== '*')
        @include('smartpack/smart-pack-lists-js')
    @endif
@endsection