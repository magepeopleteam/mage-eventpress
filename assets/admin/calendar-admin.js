/* MEP Calendar Admin JS */
jQuery(document).ready(function($) {
    'use strict';

    function initColorPickers(context) {
        $(context).find('.mep-cal-color-picker').wpColorPicker();
    }

    function refreshRuleType($row) {
        var type = $row.find('.mep-cal-day-rule-type').val() || 'weekday';
        $row.find('.mep-cal-day-rule-value-weekday').toggleClass('is-hidden', type !== 'weekday');
        $row.find('.mep-cal-day-rule-value-date').toggleClass('is-hidden', type !== 'date');
    }

    function refreshRulePreview($row) {
        var imageUrl = $.trim($row.find('.mep-cal-media-url').val() || '');
        var $preview = $row.find('.mep-cal-day-rule-preview');

        if (imageUrl) {
            $preview.addClass('has-image').css('background-image', 'url("' + imageUrl.replace(/"/g, '\\"') + '")');
        } else {
            $preview.removeClass('has-image').css('background-image', 'none');
        }
    }

    function bindRuleRow($row) {
        refreshRuleType($row);
        refreshRulePreview($row);
    }

    initColorPickers(document);

    $(document).on('change', '.mep-cal-day-rule-type', function() {
        bindRuleRow($(this).closest('.mep-cal-day-rule-item'));
    });

    $(document).on('input change', '.mep-cal-media-url', function() {
        refreshRulePreview($(this).closest('.mep-cal-day-rule-item'));
    });

    $(document).on('click', '.mep-cal-add-day-rule', function(e) {
        e.preventDefault();

        var $list = $(this).closest('.mep-cal-day-rule-list').find('.mep-cal-day-rule-items');
        var template = $('#mep-cal-day-rule-template').html() || '';
        var nextIndex = parseInt($list.attr('data-next-index'), 10);

        if (isNaN(nextIndex)) {
            nextIndex = $list.children('.mep-cal-day-rule-item').length;
        }

        template = template.replace(/__index__/g, nextIndex);

        var $row = $(template);
        $list.append($row);
        $list.attr('data-next-index', nextIndex + 1);
        bindRuleRow($row);
    });

    $(document).on('click', '.mep-cal-day-rule-remove', function(e) {
        e.preventDefault();

        var $row = $(this).closest('.mep-cal-day-rule-item');
        var $list = $row.closest('.mep-cal-day-rule-items');

        if ($list.children('.mep-cal-day-rule-item').length <= 1) {
            $row.find('input[type="text"], input[type="date"]').val('');
            $row.find('.mep-cal-day-rule-type').val('weekday');
            $row.find('.mep-cal-day-rule-weekday').val('mon');
            bindRuleRow($row);
            return;
        }

        $row.remove();
    });

    $(document).on('click', '.mep-cal-media-upload', function(e) {
        e.preventDefault();

        var $button = $(this);
        var $row = $button.closest('.mep-cal-day-rule-item');
        var frame = wp.media({
            title: (window.mepCalendarAdmin && mepCalendarAdmin.chooseImage) || 'Choose Background Image',
            button: {
                text: (window.mepCalendarAdmin && mepCalendarAdmin.useImage) || 'Use This Image'
            },
            library: {
                type: 'image'
            },
            multiple: false
        });

        frame.on('select', function() {
            var attachment = frame.state().get('selection').first().toJSON();
            $row.find('.mep-cal-media-url').val(attachment.url).trigger('change');
        });

        frame.open();
    });

    $('.mep-cal-day-rule-item').each(function() {
        bindRuleRow($(this));
    });
});
