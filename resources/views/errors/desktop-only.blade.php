@extends('layouts.app')

@section('custom-head-content')
<META HTTP-EQUIV="refresh" CONTENT="900">
@endsection

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-md-8 col-md-offset-2">
                <img style="margin:15px 0"src="{{ url('/images/logo-red.png') }}" width="150" />
                <div class="panel panel-default">
                    <div class="panel-body">
                        <div class ="alert alert-danger">
                          We're still working on optimizing the cockpit for mobile devices.
                          <br>
                          Please login with desktop/laptop computer to enjoy full functionality and exprience.
                          <br>
                          <strong>Personali Team</strong>
                        </div>
                        <img src="{{URL('/images/puppy.jpg')}}"></img>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
