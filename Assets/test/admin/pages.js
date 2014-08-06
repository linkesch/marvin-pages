module('admin - pages');

asyncTest('init() calls events()', function () {
    this.stub(marvin_pages, 'events', function () {
        ok(1, 'events() called');
        marvin_pages.events.restore();
        start();
    });

    marvin_pages.init();
});

asyncTest('init() calls editor()', function () {
    this.stub(marvin_pages, 'editor', function () {
        ok(1, 'editor() called');
        marvin_pages.editor.restore();
        start();
    });

    marvin_pages.init();
});

test('move() moves tr down if down button is clicked', function () {
    $('#qunit-fixture').html('<table id="pages">'+
        '<tr id="tr-1"><td><a class="move-down"></a></td></tr>'+
        '<tr id="tr-2"></tr>'+
    '</table>');

    marvin_pages.move({ target: $('.move-down'), preventDefault: function () {} });

    equal($('#pages tr:first').attr('id'), 'tr-2', '#tr-2 is first now');
    equal($('#pages tr:last').attr('id'), 'tr-1', '#tr-1 is 2nd now');
});

test('move() moves tr up if up button is clicked', function () {
    $('#qunit-fixture').html('<table id="pages">'+
        '<tr id="tr-1"></tr>'+
        '<tr id="tr-2"><td><a class="move-up"></a></td></tr>'+
    '</table>');

    marvin_pages.move({ target: $('.move-up'), preventDefault: function () {} });

    equal($('#pages tr:first').attr('id'), 'tr-2', '#tr-2 is first now');
    equal($('#pages tr:last').attr('id'), 'tr-1', '#tr-1 is 2nd now');
});

test('move() adds class striped-event to #pages if it is no there', function () {
    $('#qunit-fixture').html('<table id="pages"></table>');

    marvin_pages.move({ target: {}, preventDefault: function () {} });

    ok($('#pages').hasClass('striped-even'), '.striped-event added to #pages');
});

test('move() remove class striped-event from #pages if it exists', function () {
    $('#qunit-fixture').html('<table id="pages" class="striped-even"></table>');

    marvin_pages.move({ target: {}, preventDefault: function () {} });

    equal($('#pages').hasClass('striped-even'), false, '.striped-even removed from #pages');
});

asyncTest('move() calls hideMoveButtons()', function () {
    this.stub(marvin_pages, 'hideMoveButtons', function () {
        ok(1, 'hideMoveButtons() called');
        marvin_pages.hideMoveButtons.restore();
        start();
    });

    marvin_pages.move({ target: {}, preventDefault: function () {} });
});

asyncTest('move() make ajax call', function () {
    this.stub(jQuery, 'post', function () {
        ok(1, '$.post() called');
        jQuery.post.restore();
        start();
    });

    marvin_pages.move({ target: {}, preventDefault: function () {} });
});

test('hideMoveButtons() shows move buttons and hide the first up button and the last down button', function () {
    $('#qunit-fixture').html('<table id="pages">'+
        '<tbody>'+
            '<tr><td><a class="move-up"></a><a class="move-down"></a></td></tr>'+
            '<tr><td><a class="move-up hidden"></a><a class="move-down hidden"></a></td></tr>'+
            '<tr><td><a class="move-up"></a><a class="move-down"></a></td></tr>'+
        '</tbody>'+
    '</table>');

    marvin_pages.hideMoveButtons();

    equal($('#pages tr:first .move-up').hasClass('hidden'), true, 'first move-up is hidden');
    equal($('#pages tr:first .move-down').hasClass('hidden'), false, 'first move-down is visible');
    equal($('#pages tr:nth-child(2) .move-up').hasClass('hidden'), false, '2nd move-up is visible');
    equal($('#pages tr:nth-child(2) .move-down').hasClass('hidden'), false, '2nd move-down is visible');
    equal($('#pages tr:last .move-up').hasClass('hidden'), false, 'last move-up is visible');
    equal($('#pages tr:last .move-down').hasClass('hidden'), true, 'last move-down is hidden');
});

test('editor() inits medium-editor', function () {
  $('#qunit-fixture').html('<div id="page-name"></div><div id="page-content"></div>');

  marvin_pages.editor();

  equal($('#page-name').attr('data-medium-element'), 'true', '#page-name has medium-editor');
  equal($('#page-name').hasClass('medium-editor-insert-plugin'), false, '#page-name do not have insert plugin');
  equal($('#page-content').attr('data-medium-element'), 'true', '#page-content has medium-editor');
  equal($('#page-content').hasClass('medium-editor-insert-plugin'), true, '#page-content have insert plugin');
});

test('editor() returns false if there is no #page-name or #page-content', function () {
  equal(marvin_pages.editor(), false, 'false returned');
});

test('editorInput() copies content of editor to its input', function () {
  $('#qunit-fixture').html('<div id="page-name">abc</div>'+
    '<div id="page-content">def</div>'+
    '<input id="form_name">'+
    '<textarea id="form_content"></textarea>'
  );

  marvin_pages.editor();
  marvin_pages.editorInput({ target: $('#page-name') });

  equal($('#form_name').val(), 'abc', 'input content is copied');
  equal($('#form_content').val(), '', 'textarea content is not copied yet');

  marvin_pages.editorInput({ target: $('#page-content') });
  equal($('#form_content').val(), 'def', 'textarea content is copied');
});
