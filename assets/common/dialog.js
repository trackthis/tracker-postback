(function() {
  var fw=require('../lib/fw');
  fw('.show-modal').each(function(btn) {
    var dialog = btn.nextElementSibling;
    if((!dialog)||(dialog.tagName!=='DIALOG')) return;
    if(dialog.getAttribute('upgraded')) return;
    dialog.setAttribute('upgraded',1);
    var cover = document.createElement('DIV');
    cover.className = 'cover';
    dialog.parentNode.insertBefore(cover,dialog);
    dialog.parentNode.insertBefore(dialog,cover);
    // var close = document.createElement('I');
    // close.className = 'close material-icons';
    // close.innerHTML = 'close';
    // target.appendChild(close);
    fw(dialog).find('.close').on('click', function() {
      dialog.close();
    });
    fw(btn).on('click', function() {
      dialog.show();
    });
  });
})();
