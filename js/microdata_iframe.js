var ajax_url = parent.ajaxurl;
jQuery(document).ready(function () {
    var file_frame;
    if (parent.text != '') {
        eval_microdata('"' + parent.text + '"');
        jQuery('#table input[type=text]').live('blur', function () {
            load_microdata();
        });
        jQuery('#save-option').click(function () {
            update_microdata();
            parent.text = '';
        });
    }
    else {
        jQuery('#middle-notify').hide();
        load_classes();
        jQuery('#table input[type=text]').live('blur', function () {
            load_microdata();
        });
        jQuery('#schemas-classes').change(function () {
            load_schema(jQuery(this).val());

        });
        jQuery('#save-option').click(function () {
            add_microdata();
        });
    }
    jQuery('#cancel-option').click(function () {
       parent.text = '';
       window.parent.tb_remove();
    });
    jQuery('#table input[type=submit]').live('click', function () {
        var clicked = jQuery(this);
        event.preventDefault();
        //If the uploader object has already been created, reopen the dialog
        if (file_frame) {
            file_frame.open();
            return;
        }
        //Extend the wp.media object
        file_frame = wp.media.frames.file_frame = wp.media({
            title: 'Chooseee Image',
            button: {
                text: 'Chooseee Image'
            },
            multiple: false
        });
        alert('a');
        //When a file is selected, grab the URL and set it as the text field's value
        file_frame.on('select', function() {
            attachment = file_frame.state().get('selection').first().toJSON();
            jQuery('#content').val(jQuery('#content').val()+attachment.url);
        });
        //Open the uploader dialog
        file_frame.open();
    });
});
function load_classes(type, object) {
    var data = {
        action: 'load_classes_option'
    }
    jQuery.post(ajax_url, data, function (response) {
        jQuery.each(response, function (key, value) {
            jQuery('#schemas-classes').append(jQuery("<option/>", {
                value: value,
                text: value
            }));
        });
        if (type && object) {
            jQuery('#schemas-classes').val(type);
            load_schema(type, object);
        }
        else {
            load_schema(response[0], '');
        }
    }, "json");
}
function load_schema(type, object) {
    jQuery('#middle-notify').show();
    jQuery('#table').find('tr').remove();
    var data = {
        action: 'load_schema_option',
        type: type
    }
    if (type && object) {
        jQuery.post(ajax_url, data, function (response) {

            jQuery.each(response, function (key, value) {
                    jQuery('#table').append('<tr><td><label id="' + key + '">' + key + '</label></td><td><input type="text" name="' + key + '" id="' + key + '" value="' + value + '"/></td></tr>');
            });
            jQuery.each(object, function (key, value) {
                jQuery("#table input[type=text]").each(function () {
                    if (jQuery(this).attr('id') == key)
                        jQuery(this).val(value);
                });
            });
            jQuery('#html5').val(load_microdata());
            jQuery('#middle-notify').hide();
        }, "json");
    }
    else {
        jQuery.post(ajax_url, data, function (response) {
            jQuery.each(response, function (key, value) {

                    jQuery('#table').append('<tr><td><label id="' + key + '">' + key + '</label></td><td><input type="text" name="' + key + '" id="' + key + '" value="' + value + '"/></td></tr>');

            });
            jQuery('#middle-notify').hide();
        }, "json");
    }
}
function load_microdata() {
    var properties = {};
    jQuery("#table input[type=text]").each(function (key, value) {
        properties[jQuery(this).attr('id')] = jQuery(this).val();
    });
    var data = {
        action: 'load_html5_option',
        properties: JSON.stringify(properties),
        class: jQuery('#schemas-classes').val()
    };
    jQuery.post(ajax_url, data, function (response) {
        jQuery('#html5').val(response);
    }, "json");
}
function amdSubmitForm(microdata) {
    window.parent.amdMicrodataInsertIntoPostEditor(microdata);
}
function add_microdata() {
    var properties = {};
    jQuery("#table input[type=text]").each(function () {
        properties[jQuery(this).attr('id')] = jQuery(this).val();
    });
    var data = {
        action: 'crate_option',
        properties: JSON.stringify(properties),
        class: jQuery('#schemas-classes').val()
    }
    jQuery.post(ajax_url, data, function (response) {
        amdSubmitForm('Microdata id=' + response.id + ' schema=' + response.schema);
        window.parent.tb_remove();
    }, "json");
}
function update_microdata() {
    var properties = {};
    jQuery("#table input[type=text]").each(function () {
        properties[jQuery(this).attr('id')] = jQuery(this).val();
    });
    var data = {
        action: 'update_option',
        properties: JSON.stringify(properties),
        class: jQuery('#schemas-classes').val()
    }
    jQuery.post(ajax_url, data, function (response) {
        alert('Your Microdata was updated.');
        window.parent.tb_remove();
    }, "json");
}
function eval_microdata(cadena) {
    var data = {
        action: 'eval_microdata_option',
        cadena: cadena
    };
    jQuery.post(ajax_url, data, function (response) {
        if (response.status == 'success') {
            load_classes(response.type, response.object);
        } else {
            load_classes();
            parent.text ='';
        }
    }, "json");
}

