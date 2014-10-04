
window.feedbackforms = [];

(function($) {

    $(document).ready(function() {

        $('.cs_feedback_form').each(function() {
            $(this).validate({
                submitHandler: function(form) {

                    var form_id = $(form).data('id');

                    $(form).ajaxSubmit({
                        'type': 'POST',
                        'dataType': 'json',
                        'url': '/wp-admin/admin-ajax.php',
                        'data': {'action': 'CSFeedbackForms_submit', 'form_id': form_id},
                        'beforeSubmit': function(arr, $form, options) {
                            
                            window.feedbackforms[form_id + '_before']($form);
                            
                        },
                        'success': function(data) {

                            if (data.result == 'OK') {

                                if (data.form_id != undefined) {

                                    window.feedbackforms[data.form_id + '_success'](data);

                                }

                            }
                            else {

                                if (data.form_id != undefined) {

                                    window.feedbackforms[data.form_id + '_error'](data);

                                }
                                else {

                                    alert('Error! :(');

                                }

                            }

                        },
                        'error': function() {


                        }
                    });

                },
                rules: {
                },
                messages: {
                },
                errorPlacement: function(error, element) {
                    element.parent().addClass('error');
                }
            });
        });

        $('form').on('focusout', 'input[type="text"], textarea', function() {
            if (!$(this).hasClass('error')) {
                $(this).parent().removeClass('error');
            } else {
            }
        });

    });

})(jQuery);