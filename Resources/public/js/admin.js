(function($) {
    "use strict";
    var $table = $('table.table');
    var loadingCellIds = [];

    $table.on('click', 'a.msi_cmf_admin_change', function(e) {
        var $this = $(this),
            iconTrue = $this.data('icon-true'),
            iconFalse = $this.data('icon-false'),
            BadgeTrue = $this.data('badge-true'),
            BadgeFalse = $this.data('badge-false'),
            cellId = $this.closest('td').attr('id');

        if ($.inArray(cellId, loadingCellIds) !== -1) {
            return;
        }
        loadingCellIds.push(cellId);

        $this.children('span').children().html('<img src="/bundles/msicmf/img/ajax-loader2.gif" alt="0">');

        $.ajax($this.data('url'), {
            success: function() {
                if ($this.children('span').hasClass(BadgeTrue)) {
                    var i = '<i class="icon-white"><span class="hide">0</span></i>';
                    $this.children('span')
                        .removeClass(BadgeTrue)
                        .addClass(BadgeFalse)
                        .children()
                        .empty()
                        .html(i)
                        .children()
                        .removeClass(iconTrue)
                        .addClass(iconFalse);
                } else {
                    var i = '<i class="icon-white"><span class="hide">1</span></i>';
                    $this.children('span')
                        .removeClass(BadgeFalse)
                        .addClass(BadgeTrue)
                        .children()
                        .empty()
                        .html(i)
                        .children()
                        .removeClass(iconFalse)
                        .addClass(iconTrue);
                }

                loadingCellIds.splice(loadingCellIds.indexOf(cellId), 1);
            }
        });
        e.preventDefault();
    });

    $('form.form-horizontal').on('click', 'a.msi_cmf_admin_deleteUpload', function(e) {
        var $this = $(this);
        if (!window.confirm('Are you sure you want to delete this file?')) {
            return;
        }
        $.ajax($this.attr('href'), {
            success: function(data) {
                if (data.status === 'ok') {
                    $this.closest('.btn-toolbar').remove();
                }
            }
        });
        e.preventDefault();
    });

    $table.on('click', 'a.msi_cmf_admin_delete', function(e) {
        var $this = $(this);
        if (!window.confirm('Are you sure you want to delete this entry?')) {
            return;
        }
        $.ajax($this.data('url'), {type: 'POST'});
        $this.closest('tr').remove();
        e.preventDefault();
    });

    $('form#limitForm select').on('change', function() {
        $(this).closest('form').submit();
    });

    $('.btn-select-all').on('click', function(e) {
        $(this).closest('.controls').next('.control-group').find('input').prop('checked', true);
        e.preventDefault();
    });

    $('.btn-select-none').on('click', function(e) {
        $(this).closest('.controls').next('.control-group').find('input').prop('checked', false);
        e.preventDefault();
    });

    // char count for textareas

    $('textarea').on('keyup', function() {
        var $this = $(this);
        $this.siblings('div.char-count').text($this.val().length);
    });

    $(window).on('load', function() {
        $.each($('textarea'), function(i, v) {
            var $v = $(v);
            $v.siblings('div.char-count').text($v.val().length);
        });
    });

    // Helper function to add query string params to url

    jQuery.parameterize = function(url, params) {
        var url = url || window.location.href;

        if (url.match(/\?/)) {
            var hasQuery = true;
        } else {
            var hasQuery = false;
            url = url+'?';
        }

        var i = 0;
        for (var x in params) {
            if (!hasQuery && i === 0) {
                url += x+'='+params[x];
            } else {
                url += '&'+x+'='+params[x];
            }
            i++;
        }

        return url;
    };
})(jQuery);
