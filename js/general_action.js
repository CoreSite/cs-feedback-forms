(function($) {

    window.cs_actionBefore = function(form_id, form) {

        $("form[data-id='" + form_id + "'] input").attr('disabled', 'disabled');
        //submit_value = $("form[data-id='" + form_id + "'] input[type='submit']").val();
        //$("form[data-id='" + form_id + "'] input[type='submit']").val('Отправка');

        var btn = $(form).find('.btn_link').first();
        var btn_msg = btn.next('.btn-msg').first();
        var text_before = btn.data('before');

        btn
                .fadeOut(200, function() {
                    btn_msg.html(text_before).fadeIn(200);
                })
                ;

    }

    window.cs_actionSuccess = function(form_id) {

        $("[data-id='" + form_id + "']").resetForm();
        $("form[data-id='" + form_id + "'] input").removeAttr('disabled');

        var btn = $("[data-id='" + form_id + "']").find('.btn_link').first();
        var btn_msg = btn.next('.btn-msg').first();
        var text_success = btn.data('success');
        var text_default = btn.data('default');

        btn_msg
                .html(text_success)
                ;
        setTimeout(function() {
            btn_msg
                    .html('')
                    .hide()
                    ;
            btn.fadeIn(200);
        }, 3000);

    }


})(jQuery);