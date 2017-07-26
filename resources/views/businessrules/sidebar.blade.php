<li><a href="{{ url('lists/included') }}">Included</a></li>
<li><a href="{{ url('lists/excluded') }}">Excluded</a></li>

@can('view', Resource::get('smart-pack'))
    <li><a href="{{ url('smartpack') }}">Smart Pack Lists</a></li>
@endcan
@can('view', Resource::get('catalog'))
    <li><a href="{{ url('catalog') }}">Catalog</a></li>
@endcan
@can('view', Resource::get('business-logs'))
<li><a href="{{ url('lists/logs') }}">Logs</a></li>
@endcan
@can('view', Resource::get('operations'))
<li><a href="{{ url('lists/operations') }}">Operations</a></li>
@endcan
