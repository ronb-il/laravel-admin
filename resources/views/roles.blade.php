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

    <table class="table table-hover">
        <thead>
            <tr>
                <th width="20%">Role</th>
                <th width="35%">Description</th>
                <th>Permissions</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>admin</td>
                <td>Full control of Users, Business Rules.</td>
                <td>all (just not sure about reports, because of affiliateid filters)</td>
            </tr>
            <tr>
                <td>am-viewer<br/>(Account Manager Viewer)</td>
                <td>Can only view business rules. Has access to account manager reports.</td>
                <td>{{ implode(", ", $roles['am-viewer']) }}</td>
            </tr>
            <tr>
                <td>am-editor<br/>(Account Manager Editor)</td>
                <td>Can view and modify business rules. Has access to account manager reports.</td>
                <td>{{ implode(", ", $roles['am-editor']) }}</td>
            </tr>
            <tr>
                <td>customer-viewer</td>
                <td>Can only view customer reports.</td>
                <td>{{ implode(", ", $roles['customer-viewer']) }}</td>
            </tr>
            <tr>
                <td>customer-editor</td>
                <td>Can view and modify business rules.  Only has access to customer reports</td>
                <td>{{ implode(", ", $roles['customer-editor']) }}</td>
            </tr>
        </tbody>
    </table>
@endsection
