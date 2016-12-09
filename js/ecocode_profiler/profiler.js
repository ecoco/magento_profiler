'use strict';

jQuery(function ($) {
    "use strict";
    var iframe = $('<iframe style="display: none"></iframe>');
    $(document.body).append(iframe);
    $(document).on('click.setting.remove', '.file_link, a[title="Go to source"]', function (e) {
        e.preventDefault();
        var href = $(this).attr('href');
        if (href.indexOf('//') === 0 || href.indexOf('http') === 0) {
            $.get(href)
                .fail(function () {
                    var win = window.open(href, '_blank');
                    win.focus();
                });
        } else {
            iframe.attr('src', href);
        }
    });

    $(".sortable-table").stupidtable();
});
