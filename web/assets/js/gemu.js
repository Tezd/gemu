$.fn.msisdn_input = function(prefill) {
    $msisdnInput = $(this);
    var generateMsisdn = function(){
        $msisdnInput.val('0'+parseInt(Math.random()*10000000000));
    };
    if(prefill === true) {
        generateMsisdn();
    }
    $(this).parent().after($('<div class="input-field col s4"></div>')
            .append(
            $('<a class="btn-floating btn waves-effect waves-light"></a>')
                .click(generateMsisdn)
                .append(
                $('<i class="mdi-action-autorenew"></i>')

            )

        )
    );
};

$.fn.submit_button = function() {
    function simulate_wave(elem)
    {
        elem.dispatchEvent(
            new MouseEvent(
                'mousedown',
                {
                    view: window,
                    bubbles: true,
                    cancelable: true
                }
            )
        );
        elem.dispatchEvent(
            new MouseEvent(
                'mouseup',
                {
                    view: window,
                    bubbles: true,
                    cancelable: true
                }
            )
        );
    }

    function validate_controls($elem) {
        var get_validatable_element = function($el) {
            if($el.hasClass('select-dropdown')) {
                return $el.next().next();
            }
            return $el;
        };
        var isValid = true;
        var validate = function() {
            var $self = $(this);
            var val = get_validatable_element($self).val();
            if(val != null && val.length != 0) {
                $self.removeClass('invalid').addClass('valid');
                return;
            }
            $self.removeClass('valid').addClass('invalid');
            isValid = false;
        };
        $elem.find('input:text').each(validate);
        return isValid;
    }

    $(this).click(function() {
        $form = $(this).parent().parent().parent();
        $target = $form.parent().next().children(':first-child');
        if(!validate_controls($form)){
            return false;
        }
        simulate_wave($target[0]);
        $target.click();
        $('#main_screen').attr('src','http://172.19.0.2/mcb-ads');
    });
};

$.fn.create_gateway_select = function($operator){
    var $self = $(this);
    $operator.material_select();
    var operator_select = function() {
        $operator.parent().parent().slideUp(800);
        $.ajax({
            method: 'POST',
            url: 'service/'+$('#gateway').val()+'/operators',
            dataType: 'json'
        }).done(function(msg) {
            $operator.children('[value!=""]').remove();
            $operator.val("");
            for(value in msg) {
                $operator.append('<option value="'+value+'">'+msg[value]+'</option>');
            }
            $operator.material_select();
            $operator.parent().parent().slideDown(800);
        });
    };
    $self.material_select(operator_select);
};

$(document).ready(function() {
    $('#msisdn').msisdn_input(true);
    $('#submit').submit_button();
    $('#gateway').create_gateway_select($('#operator'));
});
