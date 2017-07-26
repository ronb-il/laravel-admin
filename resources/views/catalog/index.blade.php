@extends('layouts.admin')

@section('sidebar-content')
    @include('catalog/catalog-sidebar')
@endsection

@section('content')
    @if($affiliateId !== '*')
        @include('catalog/catalog-content')
    @else
        @include('partials/selectaffiliate')
    @endif
@endsection

@section('custom-javascript')
    @if($affiliateId !== '*')
        @include('catalog/catalog-js')
    @endif
@endsection