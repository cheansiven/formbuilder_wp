jQuery(document).ready(function() {
$ = jQuery;
//Polyfill
        webshims.polyfill();
        $('.fcf-form').on('submit', function(e) {
e.preventDefault();
        $form = $(this);
        $form_id = $form.attr('id').replace('fcf-', '');
        var messages;
        $form.fadeOut();
        $('.fcf.loader').fadeIn();
        var data = {
        action: 'fcf_get_messages',
                id: $form_id
        };
        $.post('', data, function(data) {
        messages = JSON.parse(data);
                //Rest Code Here
                var error = false;
                $notification = $form.find('.fcf.message');
                $notification.html('');
                var inputs = $form.find('input.fcf,textarea.fcf,select.fcf');
                for (var i = 0; i < inputs.length; i++) {
        var $input = $(inputs[i]);
                if ($input.hasClass('required')) {
        if ($input.val() == '') {
        var label = $input.data('label');
                $notification.append('<p><b>' + label + ':</b> ' + messages.required + '</p>');
                error = true;
        }
        }
        if ($input.hasClass('email')) {
        var reg = /^([a-zA-Z0-9_\.\-])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})+$/;
                if (!reg.test($input.val())) {
        var label = $input.data('label');
                $notification.append('<p><b>' + label + ':</b> ' + messages.invalid_email + '</p>');
                error = true;
        }
        }
        if ($input.hasClass('url')) {
        var reg = /(ftp|http|https):\/\/(\w+:{0,1}\w*@)?(\S+)(:[0-9]+)?(\/|\/([\w#!:.?+=&%@!\-\/]))?/;
                if (!reg.test($input.val())) {
        var label = $input.data('label');
                $notification.append('<p><b>' + label + ':</b> ' + messages.invalid_url + '</p>');
                error = true;
        }
        }
        if ($input.attr('type') == 'checkbox') {
        if ($input.hasClass('required')) {
        var name = $input.attr('name');
                var checked = $('input[name="' + name + '"]:checked');
                if (checked.length == 0) {
        var label = $input.data('label');
                $notification.append('<p><b>' + label + ':</b> ' + messages.required + '</p>');
                error = true;
        }
        }
        }
        if ($input.hasClass('number')) {
        if (!IsNumeric($input.val())) {
        var label = $input.data('label');
                $notification.append('<p><b>' + label + ':</b> ' + messages.number_error + '</p>');
                error = true;
        }
        var max_val = parseInt($input.attr('max'));
                if (typeof max_val != 'undefined') {
        if ($input.val() > max_val) {
        var label = $input.data('label');
                $notification.append('<p><b>' + label + ':</b> ' + messages.large_num + '</p>');
                error = true;
        }
        }
        var min_val = parseInt($input.attr('min'));
                if (typeof min_val != 'undefined') {
        if ($input.val() < min_val) {
        var label = $input.data('label');
                $notification.append('<p><b>' + label + ':</b> ' + messages.small_num + '</p>');
                error = true;
        }
        }

        }
        }
        if (error == true) {
        $notification.prepend('<p>' + messages.failed + '</p>');
        } else {
        var emailStr = "";
                $form.find('input[type=email]').each(function(){
        $this = $(this);
                emailStr = emailStr + "," + $this.val();
        });
                var vals = {};
                $form.find('input,select,textarea').each(function(){
                    var key = $(this).attr('name');
                    if(typeof key !== 'undefined'){
                    key = key.replace('fcf[','');
                    key = key.replace(']','');
                vals[key] = $(this).val();
                    }
        });
                var data = {
                action: 'fcf_email_process',
                        emails: emailStr,
                        form_id:$form_id,
                        fcf:vals
                };
                $.post('', data);
                $form.ajaxSubmit({
                success: function() {
                $('div.fcf.end_message').html('<p>' + messages.success + '</p>');
                        $('.fcf.loader').fadeOut();
                        if (redirect_url.length !== 0){
                window.location = redirect_url;
                }
                },
                        error: function() {
                        $('div.fcf.end_message').html('<p>' + messages.failed + '</p>');
                                $form.find(':input').prop('disabled', false);
                                $form.fadeIn();
                                $('.fcf.loader').fadeOut();
                        }
                });
        }
        });
        return false;
        });
        function IsNumeric(num) {
        return (num >= 0 || num < 0);
        }
});