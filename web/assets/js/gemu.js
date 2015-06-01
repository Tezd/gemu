/**
 * Class that listens for server side events from EventSource
 * and outputs them into log element in the window
 *
 * @param $log
 */
var streamListener = function ($log) {
    var source;
    var $_log = $log;
    this.listen = function (transactionId) {
        if (source instanceof EventSource) {
            source.close();
        }
        $_log.children().remove();
        source = new EventSource("logs.php?transactionId=" + transactionId);
        source.onmessage = function (e) {
            $_log.append('<p>' + e.data + '</p>');
        };
    };
};

/**
 * Saves transaction data, emulate url, attaches event listener
 * @param baseUri
 */
var emulate = function (baseUri) {
    var parsedUrl = $('<a>', {
        href: baseUri
    })[0];
    var params = {};
    var transactionId;
    var url;

    var composeParams = function () {
        var _buildParams = $.param({
            emulate: 1,
            rid: transactionId
        });
        return parsedUrl.search ? parsedUrl.search + '&' + _buildParams :
        '?' + _buildParams;
    };

    var createLink = function () {
        url = parsedUrl.protocol + '//' + parsedUrl.host + parsedUrl.pathname + composeParams() + parsedUrl.hash;
    };

    var saveTransaction = function () {
        transactionId = $.ajax({
            method: 'POST',
            async: false,
            url: 'save/transaction',
            dataType: 'json',
            data: params
        }).responseJSON.id;
    };

    this.addParam = function (name, val) {
        params[name] = val;
    };

    this.build = function () {
        saveTransaction();
        createLink();
        return this;
    };

    this.attach = function (listener) {
        listener.listen(transactionId);
        return this;
    };

    this.url = function () {
        return url;
    };
};

/**
 * Creates input with msisdn and button that can repopulate this input.
 * @param prefill indicates wheter we want to populate input or leave it empty
 */
$.fn.msisdn_input = function (prefill) {
    $msisdnInput = $(this);
    var generateMsisdn = function () {
        $msisdnInput.val('0049' + parseInt(Math.random() * 10000000000));
    };
    if (prefill === true) {
        generateMsisdn();
    }
    $(this).parent().after($('<div class="input-field col s4"></div>')
        .append(
        $('<a class="btn-floating btn waves-effect waves-light"></a>')
            .click(generateMsisdn)
            .append(
            $('<i class="mdi-action-autorenew"></i>')

        )

    ));
};

/**
 * Adds a logic to submit button in settings section.
 * This code validates inputs inside of settings section.
 * If all elements are valid:
 * 1) creates emulate link for iframe
 * 2) open log section
 * 3) create ripple effect for log section.
 */
$.fn.submit_button = function () {
    function simulate_wave(elem) {
        elem.dispatchEvent(
            new MouseEvent(
                'mousedown', {
                    view: window,
                    bubbles: true,
                    cancelable: true
                }));
        elem.dispatchEvent(
            new MouseEvent(
                'mouseup', {
                    view: window,
                    bubbles: true,
                    cancelable: true
                }));
    }

    function get_real_element($el) {
        if ($el.hasClass('select-dropdown')) {
            return $el.next().next();
        }
        return $el;
    }

    function validate_controls($elem) {
        var isValid = true;
        var validate = function () {
            var $self = $(this);
            var val = get_real_element($self).val();
            if (val != null && val.length != 0) {
                $self.removeClass('invalid').addClass('valid');
                return;
            }
            $self.removeClass('valid').addClass('invalid');
            isValid = false;
        };
        $elem.find('input:text').each(validate);
        return isValid;
    }

    /**
     * @param $elem
     * @returns {*}
     */
    function create_link($elem) {

        var emulator = new emulate(
            $elem.find('input:text[data-url-base]').val());

        function stripInfo() {
            var $self = get_real_element($(this));
            emulator.addParam($self.attr('name'), $self.val());
        }

        $elem.find('input:text:not([data-url-base])').each(stripInfo);
        $elem.find('input[type="radio"][name]:checked').each(stripInfo);
        return emulator.build().attach(new streamListener($('#logs'))).url();
    }

    $(this).click(function () {
        $form = $(this).parent().parent().parent();
        $target = $form.parent().next().children(':first-child');
        if (!validate_controls($form)) {
            return false;
        }
        simulate_wave($target[0]);
        $target.click();
        $('#main_screen').attr('src', create_link($form));
    });
};

/**
 * @todo add caching
 * @todo add f5 refill of operator select
 * Adds slide effect and ajax fill for operator select based on gateway select
 * @param $operator
 */
$.fn.create_gateway_select = function ($operator) {
    var $self = $(this);
    $operator.material_select();
    var operator_select = function () {
        $operator.parent().parent().slideUp(800);
        $.ajax({
            method: 'POST',
            url: 'service/' + $('#gateway').val() + '/operators',
            dataType: 'json'
        }).done(function (msg) {
            $operator.children('[value!=""]').remove();
            $operator.val("");
            for (value in msg) {
                $operator.append('<option value="' + value + '">' + msg[value] + '</option>');
            }
            $operator.material_select();
            $operator.parent().parent().slideDown(800);
        });
    };
    $self.material_select(operator_select);
};

$(document).ready(function () {
    $('#msisdn').msisdn_input(true);
    $('#submit').submit_button();
    $('#gateway').create_gateway_select($('#operator'));
});
