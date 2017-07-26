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
                    <div class="panel-heading">
                        <h3 class="panel-title">Whoops ;)</h3>
                    </div>
                    <div class="panel-body">
                        <div class ="alert alert-danger">
                          <strong> Something went wrong</strong> <br>
                           Please try to <a href="{{URL('/login')}}">login</a> again. If the problem persists, contact your account manager.
                        </div>
                        <img src="{{URL('/images/puppy.jpg')}}"></img>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
