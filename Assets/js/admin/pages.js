var marvin_pages = function () {
    return {
        init: function () {
            marvin_pages.events();
        },

        events: function () {
            $(document).on('click', '#pages .move-up, #pages .move-down', marvin_pages.move);
        },

        move: function (e) {
            var $a = $(e.target),
                $tr = $a.parents('tr'),
                $table = $('#pages'),
                $sibling;

            e.preventDefault();

            if ($a.hasClass('move-up')) {
                $sibling = $tr.prev();
                $sibling.before($tr);
            } else if ($a.hasClass('move-down')) {
                $sibling = $tr.next();
                $sibling.after($tr);
            }

            if ($table.hasClass('striped-even')) {
                $table.removeClass('striped-even');
            } else {
                $table.addClass('striped-even');
            }

            marvin_pages.hideMoveButtons();

            $.post($a.attr('href'));
        },

        hideMoveButtons: function () {
            $('#pages .move-up, #pages .move-down').removeClass('hidden');

            $('#pages tbody tr:first .move-up').addClass('hidden');
            $('#pages tbody tr:last .move-down').addClass('hidden');
        }
    };
}();

$(function () {
    marvin_pages.init();
});
