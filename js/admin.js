// Repeater fields: add / remove list items in the block editor.
(function () {
  'use strict';
  var counter = Date.now();

  document.addEventListener('click', function (e) {
    var addBtn = e.target.closest('[data-add]');
    if (addBtn) {
      var key = addBtn.getAttribute('data-add');
      var tpl = document.getElementById('tpl-' + key);
      var list = document.querySelector('[data-list="' + key + '"]');
      if (tpl && list) {
        var html = tpl.innerHTML.split('__i__').join('new' + (counter++));
        var temp = document.createElement('div');
        temp.innerHTML = html.trim();
        var node = temp.firstElementChild;
        if (node) {
          list.appendChild(node);
          var first = node.querySelector('input, textarea, select');
          if (first) first.focus();
        }
      }
      return;
    }

    var delBtn = e.target.closest('[data-remove]');
    if (delBtn) {
      var item = delBtn.closest('[data-item]');
      if (item) item.remove();
      return;
    }

    if (e.target.closest('#sideToggle')) {
      document.body.classList.toggle('side-open');
      return;
    }
    if (e.target.closest('#sideBackdrop')) {
      document.body.classList.remove('side-open');
    }
  });
})();
