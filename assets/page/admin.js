window._      = require('../lib/fw');
window._.ajax = require('../lib/ajax');
window.rv     = require('../lib/rivets');

var q    = require('../lib/query'),
    data = q.decode(window.location.search||'');

function fancyDialog(target) {
  if(Array.isArray(target)) return target.map(fancyDialog);
  if(target.getAttribute('upgraded')) return true;
  target.setAttribute('upgraded',1);
  var cover = document.createElement('DIV'),
      close = document.createElement('I');
  cover.className = 'cover';
  close.className = 'close material-icons';
  close.innerHTML = 'close';
  target.parentNode.insertBefore(cover,target);
  target.parentNode.insertBefore(target,cover);
  target.appendChild(close);
  _(target).find('.close').on('click', function() {
    target.close();
  });
  return true;
}

function accountDelete( event, context ) {
  var args = [].slice.call(arguments),
      el   = this;
  _(el.parentNode).find('dialog').each(function(dialog) {
    fancyDialog(dialog);
    dialog.show();
  });
}

function accountEdit( event, context ) {
  var args = [].slice.call(arguments);
  console.log(this, args);
}

rv.data.api.accounts = [];
_.ajax({ 'uri': '/api/v1/accounts', data: data }, function(response) {
  if(response.status !== 200) return;
  while(response.data.length) {
    var acc    = response.data.shift();
    acc.delete = accountDelete;
    acc.edit   = accountEdit;
    rv.data.api.accounts.push(acc);
  }
});


console.log(data);
console.log(_('dialog'));
