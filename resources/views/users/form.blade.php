@extends('layouts.admin')

@section('sidebar-content')
    @can('edit', Resource::get('users'))
    <li><a href="{{ url('/users') }}">Users</a></li>
    <li><a href="{{ url('/roles') }}">Roles &amp; Permissions</a></li>
    @endcan
@endsection

@section('content')
<div class="panel panel-default" style="width:700px">
    <div class="panel-heading">{{ ($user->id) ? 'Edit User' : 'New User' }}</div>
    <div class="panel-body">
    {!! Form::model($user, $formParams + ['name' => 'users-form', 'id' => 'users-form', 'class' => 'form-horizontal', 'role' => 'form']) !!}
          <div class="form-group">
          @if (count($errors)>0)
          <div class="message error">{!! Html::ul($errors->all()) !!}</div>
          @endif
            <label class="control-label col-sm-2" for="name">Name:</label>
            <div class="col-sm-9">
              {{ Form::text('name', null, ['class' => "form-control", 'placeholder' => "Enter name", 'id' => "name"]) }}
            </div>
          </div>
          <div class="form-group">
            <label class="control-label col-sm-2" for="email">Email:</label>
            <div class="col-sm-9">
             {{ Form::text('email', null, ['class' => "form-control", 'placeholder' => "Enter email", 'id' => "email"]) }}
            </div>
          </div>
          <div class="form-group">
            <label class="control-label col-sm-2" for="pwd">Password:</label>
            <div class="col-sm-9">
              <input type="password" class="form-control" id="pwd" placeholder="Enter password" name="password" autocomplete="off">
            </div>
          </div>
          <div class="form-group">
            <label class="control-label col-sm-2" for="roles">Roles:</label>
            <div class="col-sm-9">
              {!! Form::select('roles', $roles, $user->permissions['roles'][0], ['id' => 'roles', 'class' => 'form-control']) !!}
            </div>
          </div>
          <div class="form-group">
            <label class="control-label col-sm-2" for="affiliates">Affiliates:</label>
            <div class="col-sm-9">
              {!! Form::select("affiliates[]", $affiliates, isset($user->permissions['affiliates']) ? $user->permissions['affiliates'] : null , ['id' => 'affiliates', 'class' => 'form-control select2', 'multiple' => "multiple"]) !!}
            </div>
          </div>
          {!! Form::close() !!}
          <div class="form-group">
              <button type="submit" class="btn btn-primary" onclick="document.getElementById('users-form').submit();">{{ ($user->id) ? 'Update' : 'Add New' }} User</button>
              @if($user->id)
              {!! Form::open(['action' => ['UsersController@destroy', $user->id], 'method' => 'delete', 'style' => 'display:inline-block']) !!}
                <button type="submit" class="btn btn-danger" onclick="return confirm('Are you sure you would like to delete this user?');">Delete User</button>
              {!! Form::close() !!}
              @endif
              <a href="{{ url('/users') }}" class="btn btn-default">Cancel</a>
          </div>
    </div>
</div>
@endsection

@section('custom-javascript')
<script type="text/javascript">
    $('.select2').select2({
        placeholder : ''
    });
</script>
@endsection
