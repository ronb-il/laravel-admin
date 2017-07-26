@extends('layouts.admin')

@section('sidebar-content')
    @can('edit', Resource::get('users'))
    <li>
        <a href="{{ url('/users') }}">Users</a>
    </li>
    <li>
        <a href="{{ url('/roles') }}">Roles &amp; Permissions</a>
    </li>
    @endcan
@endsection

@section('content')
<div class="row">
    <a class="btn btn-primary pull-right" href="{{ url('/users/create') }}" style="margin-bottom:20px">New User</a>
</div>
<div class="row">
<table class="table table-hover dataTables_wrapper form-inline dt-bootstrap no-footer" id="users-table">
    <thead>
    <tr>
        <th width="30%">Username</th>
        <th width="30%">Affiliates</th>
        <th>Roles</th>
        <th>Last Updated</th>
    </tr>
    </thead>
    <tbody>
    @foreach($users as $user)
    <tr>
        <td><a href="{{ URL::to('users/' . $user->id . '/edit') }}">{{ $user->email }}</a></td>
        <td>{{ isset($user->permissions['affiliates']) ? implode(", ", $user->permissions['affiliates']) : 'all' }}</td>
        <td>{{ isset($user->permissions['roles']) ? implode(", ", $user->permissions['roles']) : '' }}</td>
        <td>{{ $user->updated_at }}
    </tr>
    @endforeach
    </tbody>
</table>
</div>
@endsection

@section('custom-javascript')
    <script src="{{ url('scripts/datatables.js') }}" type="text/javascript"></script>
    <script src="{{ url('scripts/dataTables.bootstrap.js') }}" type="text/javascript"></script>

    <script type="text/javascript">
    $('#users-table').dataTable( {
      "columnDefs": [ {
        "targets": null,
        "data": null,
      } ]
    } );
    </script>
@endsection
