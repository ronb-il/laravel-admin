<li><a href="{{ url('/rules') }}">Rules set</a></li>
<li><a href="{{ url('/variations/admin') }}">Edit UX elements</a></li>
@can('view', Resource::get('behavioral-logs'))
    <li><a href="{{ url('rules/logs') }}">Logs</a></li>
@endcan
