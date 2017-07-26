<script type="text/javascript">
    //Example: Listen to sessionchanged event in your own application section
    /*window.addEventListener('sessionchanged', function (e) {
        console.log('affiliate in session changed');
    });*/
    window.onSessionChangeSuccess = function(id){
        var evt = new CustomEvent('sessionchanged', {'detail' : {'customerId' : id}});
        window.dispatchEvent(evt);
    }
</script>
<form class="form-inline" method="POST">
    @if(count($affiliates) > 1)
    <div class="control-group">
        {!! csrf_field() !!}
        <select class="form-control" id="inputAffiliateId" name="chgid" onchange="$.post('/auth/change', $(this).closest('form').serialize(), function(res){window.onSessionChangeSuccess($('#inputAffiliateId').val());});">
            <option value="{{ $selectedAffiliateId }}">All Accounts</option>
            @foreach ($affiliates as $id => $affiliate)
                @if ($selectedAffiliateId == $id)
                    <option value="{{ $affiliate[$hashedIDKeyName] }}" selected>{{ $affiliate['name'] }}</option>
                @else
                    <option value="{{ $affiliate[$hashedIDKeyName] }}">{{ $affiliate['name'] }}</option>
                @endif
            @endforeach
        </select>
    </div>
    @else
        <input type="hidden" id="inputAffiliateId" name="chngid" value="{{ $affiliates[$selectedAffiliateId][$hashedIDKeyName] }}">

    @endif
</form>


