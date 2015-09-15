var marvin_pages = function () {
    var editors = {},
        self;

    return {
        $table: $('#pages'),

        init: function () {
            self = this;

            marvin_pages.events();
            marvin_pages.editor();
        },

        events: function () {
            $(document).on('click', '#pages .move-up, #pages .move-down', marvin_pages.move);
            $(document).on('input', '#page-name, #page-content', marvin_pages.editorInput);
        },

        move: function (e) {
            var $a = $(e.target),
                $tr = $a.parents('tr'),
                $sibling;

            e.preventDefault();

            if ($a.hasClass('move-up')) {
                $sibling = $tr.prev();
                $sibling.before($tr);
            } else if ($a.hasClass('move-down')) {
                $sibling = $tr.next();
                $sibling.after($tr);
            }

            if (self.$table.hasClass('striped-even')) {
                self.$table.removeClass('striped-even');
            } else {
                self.$table.addClass('striped-even');
            }

            marvin_pages.hideMoveButtons();

            $.post($a.attr('href'));
        },

        hideMoveButtons: function () {
            self.$table.find('.move-up, .move-down').removeClass('hidden');

            self.$table.find('tbody tr:first .move-up').addClass('hidden');
            self.$table.find('tbody tr:last .move-down').addClass('hidden');
        },

        editor: function () {
            if ($('#page-name, #page-content').length) {
                editors.name = new MediumEditor('#page-name', {
                    buttonLabels: 'fontawesome',
                    disableReturn: true,
                    disableToolbar: true
                });
                editors.content = new MediumEditor('#page-content', {
                    buttonLabels: 'fontawesome'
                });

                $('#page-content').mediumInsert({
                    editor: editors.content,
                    addons: {
                        images: {
                            imagesUploadScript: '/admin/pages/file/upload',
                            imagesDeleteScript: '/admin/pages/file/delete'
                        },
                        embeds: {}
                    }
                });
            } else {
                return false;
            }
        },

        editorInput: function (e) {
            var $div = $(e.target),
                name = $div.attr('id').split('page-')[1];

            $('#form_'+ name).val(editors[name].serialize()['page-'+ name].value);
        }
    };
}();

$(function () {
    marvin_pages.init();
});
