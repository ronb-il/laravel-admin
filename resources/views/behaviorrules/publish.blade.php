<script type="text/javascript">
    $(document).ready(function(){
        publish = function(){
            $.ajax({
                url: "/rules/rule-set/publish/affiliate/{{$affiliate_id}}/set/{{$rule_set_id}}" ,
                type: "GET",
                dataType: "json",
                success: function(data){
                    if(data.status == 'true'){
                        alert("publish succeed.");
                        window.location.hash = "";
                        unBindUnSavedChangesAlert();
                        location.reload();                        
                    }
                    else{
                        displayError(data.message);
                    }
                },
                error: function(e, xhr){
                    alert("Fail to publish: is web sever up?");
                }});
        };
    });
</script>

<div class="box grid round" id="simulation-transactions-tbl-wrapper">
   Are you sure you want to publish changes?
</div>
<br>
<div class="box">
    <button onclick="publish();" class="btn rule-set-publish btn-danger">Publish</button>
    <button data-dismiss="modal" class="btn btn-default">Cancel</button>
</div>
