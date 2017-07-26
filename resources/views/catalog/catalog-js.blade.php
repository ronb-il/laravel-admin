<script>
var hide_errors = function() {
     $('#notifier').fadeOut('slow');
}
var notify = function(type,msg) {
    $('#notifier').removeClass()
           .addClass(type)
           .html(msg)
           .fadeIn("fast",function() {
               setTimeout(hide_errors,5000)
                   });
}
</script>