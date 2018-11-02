function reloadPage(url) {
    'use strict';
    window.location.href = url;
}

function convert2id(idString, expectedArrayLength) {

    var idArray = idString.split('_');

    if (idArray.length === expectedArrayLength) {
        return idArray[1];
    }

    return 0;

}

//
// Settings
PNotify.prototype.options.styling = 'bootstrap3';
PNotify.prototype.options.styling = 'fontawesome';
var stack_bottomright = {'dir1': 'up', 'dir2': 'left', 'push': 'top'};

function showMultipleNotifies(data_array, status) {
    'use strict';

    if (typeof data_array === 'undefined') {
        return;
    }

    $.each(data_array, function (key, val) {
        showNotify(val, '', status);
    });

}

function showNotify(var_title, var_text, alert_type) {
    'use strict';

    new PNotify({
        title : var_title,
        text : var_text,
        addclass : 'stack-bottomright',
        stack : stack_bottomright,
        type : alert_type
    });

}

function showErrorNofity() {
    'use strict';

    var errors = $('.errorNotification').length;
    if (errors > 0) {

        $('.errorNotification').each(function (index, value) {
            showNotify($(this).val(), '', 'error');
        });

    }

}

function showSuccessNofity() {
    'use strict';

    var errors = $('.successNotification').length;
    if (errors > 0) {

        $('.successNotification').each(function (index, value) {
            showNotify($(this).val(), '', 'success');
        });

    }

}

$(document).ready(function () {
    'use strict';
    showErrorNofity();
    showSuccessNofity();
});

function updateNotifyStatus(notify_id) {
    'use strict';

    $.post(
        'ajax.php?site=notify',
        {
            action : 'seen',
            notify_id : notify_id
        },
        function (data, status) {}
    );

}

function checkbox(cup_id) {
    'use strict';
    var checkbox = document.getElementById('checkin_box').checked;
    if (checkbox === true) {
        $('#enter_cup_container').html('<a class="list-group-item alert-info center" href="index.php?site=cup&amp;action=checkin&amp;id=' + cup_id + '">Team Check-In</a>');
    } else {
        $('#enter_cup_container').html('<span class="list-group-item alert-info center">Team Check-In</span>');
    }
    return true;
}

function showSelect(value) {
    'use strict';
    window.location.href = value + '#content';
    return true;
}

var timerHandle;

function countdown(timeleft, div_id) {
    'use strict';

    var timeleft_local,
        days,
        hours,
        minutes,
        seconds;

    timeleft_local = timeleft;

    days = Math.floor(timeleft_local / (24 * 3600));
    timeleft_local = timeleft_local % (24 * 3600);
    hours = Math.floor(timeleft_local / (60 * 60));
    timeleft_local = timeleft_local % (60 * 60);
    minutes = Math.floor(timeleft_local / 60);
    timeleft_local = timeleft_local % 60;
    seconds = timeleft_local % 60;

    if (days < 0) {
        days = 0;
    }

    if (hours < 0) {
        hours = 0;
    }

    if (minutes < 0) {
        minutes = 0;
    }

    if (seconds < 0) {
        seconds = 0;
    }

    $('#' + div_id + 'Days').html(days);
    $('#' + div_id + 'Hours').html(hours);
    $('#' + div_id + 'Minutes').html(minutes);
    $('#' + div_id + 'Seconds').html(seconds);

    timerHandle = setTimeout('countdown(' + --timeleft + ', \'' + div_id + '\')', 1000);

}

$(function () {
    'use strict';

    $('[data-toggle="tooltip"]').tooltip();


});

function isUrl(url) {
    'use strict';
    return /^(https?|s?ftp):\/\/(((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:)*@)?(((\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5]))|((([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.)+(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.?)(:\d*)?)(\/((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)+(\/(([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)*)*)?)?(\?((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)|[\uE000-\uF8FF]|\/|\?)*)?(#((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)|\/|\?)*)?$/i.test(url);
}

function isEmail(email) {
    'use strict';
    var regex = /^([a-zA-Z0-9_.+-])+\@(([a-zA-Z0-9-])+\.)+([a-zA-Z0-9]{2,4})+$/;
    return regex.test(email);
}