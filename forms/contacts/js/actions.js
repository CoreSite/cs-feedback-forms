(function ($){
 
  var submit_value = '';
  
  window.feedbackforms['contacts_success'] = function(data) {
   
    alert('Success!');
    
  },
  
  window.feedbackforms['contacts_error'] = function(data) {
    
    alert('Error!');
    
  },
  
  
  window.feedbackforms['contacts_before'] = function(form) {
        
    var form_id = $(form).data('id');
    
    alert('Before!');
    
  }
  
  
})(jQuery);