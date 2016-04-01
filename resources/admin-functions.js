(function($) {
    $(document).ready(function() {
        //Code Here

        //Upload
        var custom_uploader;
        $('.upload_btn').click(function(e) {
            e.preventDefault();
            $target = $(this).data('target');
            custom_uploader = wp.media.frames.file_frame = wp.media({
                title: 'Choose Files',
                button: {
                    text: 'Choose Files'
                },
                multiple: false
            });
            custom_uploader.on('select', function() {
                attachment = custom_uploader.state().get('selection').first().toJSON();
                $('#'+$target).val(attachment.url);
            });
            custom_uploader.open();
        });

        if (typeof fcf_use == 'undefined') {
            return;
        }
        var form_code = $('input[name=form_code]').val();
        if (form_code == '') {
            form_code = '{}';
        }
        form_code = JSON.parse(form_code);
        var form_builder = new Formbuilder({
            selector: '.fcf_form_builder',
            bootstrapData: form_code.fields
        });
        if (!$.isEmptyObject(form_code)) {
            generateNewShortcodes(form_code);
        }
        form_builder.on('save', function(payload) {
            $('input[name=form_code]').val(payload);
            var elems = JSON.parse(payload);
            if (!$.isEmptyObject(elems)) {
                generateNewShortcodes(elems);
            }
        });
        //Code Here End
    });
})(jQuery);


function generateNewShortcodes(jsonVar) {
    var shortcode_cont = $('.shortcodes_cont');
    shortcode_cont.html('');
    var elems = jsonVar;
    elems = elems.fields;
    var new_shortcodes = "";
    for (var i = 0; i < elems.length; i++) {
        elem = elems[i];
        if (elem.field_type != 'file' && elem.field_type != 'section_break') {
            new_shortcodes += '<li><button class="button button-primary shortcode_btn" data-shortcode="[fcf id=' + elem.cid + ']">' + elem.label + '</button></li>';
        }
    }
    shortcode_cont.html(new_shortcodes);
    reListenToEvents();
}

function appendInEditor(val) {
    tinyMCE.activeEditor.selection.setContent(val);
}

function reListenToEvents() {
    $('.shortcode_btn').on('click', function(e) {
        e.preventDefault();
        appendInEditor($(this).data('shortcode'));
    });
}