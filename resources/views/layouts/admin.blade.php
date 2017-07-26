<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <title>Personali Cockpit</title>

    <!-- Custom CSS -->
    <link href="{{ url('styles/variations.css') }}" rel="stylesheet" media="screen">

    <!-- Frontend CSS -->
    <link href="{{ url('styles/frontend.css') }}" rel="stylesheet">

    <link href="{{ url('styles/datatables.css') }}" rel="stylesheet">
    <link href="{{ url('styles/dataTables.bootstrap.css') }}" rel="stylesheet">

    <link href="{{ url('styles/select2.css') }}" rel="stylesheet" media="screen">
    <link href="{{ url('styles/select2-bootstrap.css') }}" rel="stylesheet">
    <link href="{{ url('styles/bootstrap-editable.css') }}" rel="stylesheet">

    <link href="{{ url('styles/retailer-lists.css') }}" media="screen" rel="stylesheet" type="text/css" />

    <!-- Custom Fonts -->
    <link href="https://fonts.googleapis.com/css?family=Lato:100,300,400,700" rel='stylesheet' type='text/css'>

    @yield('custom-styles')
    <style>
        body {
            font-family: 'Lato';
        }

        .fa-btn {
            margin-right: 6px;
        }

        .select2-search-choice-close {
            background: url('{{ url('images/select2.png') }}') right top no-repeat;
        }
    </style>


    <script src="{{ url('scripts/frontend.js') }}"></script>
    <script src="{{ url('scripts/bootstrap-tooltip.js') }}"></script>
    <script src="{{ url('scripts/bootstrap-popover.js') }}"></script>
    <script src="{{ url('scripts/bootstrap-editable.js') }}"></script>

    <script src="{{ url('scripts/select2.js') }}"></script>
    <script src="{{ url('scripts/jquery.url.js') }}"></script>
    <script src="{{ url('scripts/jquery.base64.js') }}"></script>
    <script src="{{ url('scripts/jquery.sortable.js') }}"></script>
    <script src="{{ url('scripts/bootstrap-validator.js') }}"></script>

    @yield('custom-javascript-top')
</head>
<body>
<div id="wrapper">
    {!! Analytics::render() !!}
    @if (Auth::check())
        <!-- Navigation -->
        <nav class="navbar navbar-default navbar-fixed-top" role="navigation" style="margin-bottom:0">
            <div class="navbar-header">
                <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
                    <span class="sr-only">Toggle navigation</span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                </button>
                <div class="navbar-brand-wrapper">
                <a class="navbar-brand" href="{{ url('/') }}">
                    <img src="{{ url('/images/logo-red.png') }}" width="150" height="29" />
                </a>
                </div>
            </div>

            <ul class="nav navbar-nav" aria-expanded="false">
                @can('view', Resource::get('reporting'))
                <li class="nav"><a href="{{ url('/reports') }}" {{ (Request::segment(1) == 'reports') ? 'class=active active' : '' }}>Reporting</a></li>
                @endcan
                @can('view', Resource::get('business-rules'))
                <li class="nav"><a href="{{ url('/lists/included') }}" {{ (Request::segment(1) == 'lists') ? 'class=active active' : '' }}>Business Rules</a></li>
                @endcan
                @can('view', Resource::get('behavior-rules'))
                <li class="nav"><a href="{{ url('/rules') }}" {{ (Request::segment(1) == 'rules') ? 'class=active active' : '' }}>Behavioural Rules</a></li>
                @endcan
                @can('view', Resource::get('variations'))
                <li class="nav"><a href="{{ url('/variations') }}" {{ (Request::segment(1) == 'variations') ? 'class=active' : '' }}>Lab</a></li>
                @endcan
                @can('view', Resource::get('users'))
                <li class="nav"><a href="{{ url('/users') }}" {{ (Request::segment(1) == 'users') ? 'class=active active' : '' }}>Users &amp; Permissions</a></li>
                @endcan
                @can('view', Resource::get('site-map'))
                <li class="nav"><a href="{{ url('/insites') }}" {{ (Request::segment(1) == 'insites') ? 'class=active active' : '' }}>Insites</a></li>
                @endcan
            </ul>
            <!-- /.navbar-header -->

            <!-- Left Side Of Navbar -->

            <ul class="nav navbar-top-links navbar-right" aria-expanded="false">
                <!-- /.dropdown -->
                <li class="dropdown">
                    <a class="dropdown-toggle" data-toggle="dropdown" href="#">
                        {{ Auth::user()->name }}<!-- <i class="fa fa-user fa-fw"></i>-->  <i class="fa fa-caret-down"></i>
                    </a>
                    <ul class="dropdown-menu dropdown-user">
                        <li class="">
                            <a href="{{ url('/logout') }}" onclick="ga('send', 'event','logout', 'click');">
                                <i class="fa fa-sign-out fa-fw"></i> Logout
                            </a>
                        </li>
                    </ul>
                    <!-- /.dropdown-user -->
                </li>
                <!-- /.dropdown -->
            </ul>

            <!-- /.navbar-top-links -->


            <div class="navbar-default sidebar" id = "navbar-default-sidebar-container" role="navigation">
                <a href="#menu-toggle" id="menu-toggle"></a>
                <div class="sidebar-nav navbar-collapse" id="side-menu-container">
                        <ul class="nav" id="side-menu">
                            <li class="sidebar-search">
                                @include('partials.affiliates')
                            </li>
                            @yield('sidebar-content')
                            @include('sidebar')
                        </ul>
                    </div>
                    <!-- /.sidebar-collapse -->
                </div>
            <!-- /.navbar-static-side -->
        </nav>
    @endif
        <div id="page-content-wrapper" id="sidebar-wrapper">
            <div id="page-wrapper">
                @yield('content')
            </div>
        </div>
        <!-- /#page-wrapper -->
    <script type="text/javascript">
        // Menu Toggle Script
        $("#menu-toggle").click(function(e) {
            e.preventDefault();
            $("#menu-toggle").toggleClass("toggled");
            $("#page-wrapper").toggleClass("toggled");
            $("#side-menu-container").toggleClass("toggled");
            $("#navbar-default-sidebar-container").toggleClass("toggled");
        });

        idleMax = 27;// Logout after 27 minutes of IDLE
        idleTime = 0;

        $(document).ready(function () {
            var idleInterval = setInterval("timerIncrement()", 60000);
            $(this).mousemove(function (e) {idleTime = 0;});
            $(this).keypress(function (e) {idleTime = 0;});
        })

        function timerIncrement() {
            idleTime = idleTime + 1;
            if (idleTime > idleMax) {
                window.location="{{ url('/logout') }}";
            }
        }
    </script>

    @yield('custom-javascript')
</body>
</html>
