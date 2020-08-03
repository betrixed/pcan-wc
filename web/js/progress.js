
$(document).ready(function(){
 $('#upfile').submit(function(event){
    
  var fval = $('#file').val();
  if(fval)
  {
   event.preventDefault();
   $(this).ajaxSubmit({
    target: '#image_status',
    dataType: 'text/html',
    beforeSubmit:function(){
     $('.progress-bar').width('50%');
    },
    uploadProgress: function(event, position, total, percentageComplete)
    {
     $('.progress-bar').animate({
      width: percentageComplete + '%'
     }, {
      duration: 1000
     });
    },
    complete:function(data){
        var update = $('#image_status');
        update.html(data.responseText);
        
        update.show();
        post_imagelist();
        document.refresh;
    },
    resetForm: true
   });
  }
  return false;
 });
});


