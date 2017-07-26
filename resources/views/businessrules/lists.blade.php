@extends('layouts.admin')

@section('sidebar-content')
    @include('businessrules/sidebar')
@endsection

@section('content')
    @if($affiliateId !== '*')
        @include('businessrules/lists-content')
    @else
        @include('partials/selectaffiliate')
    @endif
@endsection

@section('custom-javascript')
    @if($affiliateId !== '*')
        @include('businessrules/lists-js')
    @endif
@endsection
