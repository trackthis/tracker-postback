window._      = require('../lib/fw');
window._.ajax = require('../lib/ajax');
window.rv     = require('../lib/rivets');

var q      = require('../lib/query'),
    data   = q.decode(window.location.search||'');

// Prepare rv data
rv.data.form.account = { isAdmin: false };
rv.data.form.tokenid = token.id;
rv.data.form.rules   = [];

// Upgrade a dialog after rivets
function fancyDialog(target) {
  if(Array.isArray(target)) return target.map(fancyDialog);
  if(target.getAttribute('upgraded')) return true;
  target.setAttribute('upgraded',1);
  var cover = document.createElement('DIV');
  // var close = document.createElement('I');
  cover.className = 'cover';
  // close.className = 'close material-icons';
  // close.innerHTML = 'close';
  target.parentNode.insertBefore(cover,target);
  target.parentNode.insertBefore(target,cover);
  // target.appendChild(close);
  _(target).find('.close').on('click', function() {
    target.close();
  });
  return true;
}

// RV actions
rv.data.form.addrule = function( event, context ) {
  context.form.rules.push({
    'id'       : '', // TODO
    'token'    : context.form.tokenid,
    'account'  : '', // TODO
    'remote'   : '',
    'tracker'  : '',
    'translate': ''
  });
};
rv.data.form.delrule = function( event, context ) {
  var btn = event.target;
  while(btn.tagName!=='BUTTON') btn = btn.parentNode;
  var dialog = btn.value === 'confirm' ? btn : btn.nextElementSibling;
  while(dialog.tagName !== 'DIALOG') dialog = dialog.parentNode;

  // Confirmed = go ahead
  if ( btn.value === 'confirm' ) {
    dialog.close();
    // TODO: API CALL
    var index = context['%rule%'];
    context.form.rules.splice(index,1);
    return false;
  }

  // Show the confirmation dialog
  fancyDialog(dialog);
  dialog.show();
  return false;
};
rv.data.form.saverule = function( event, context ) {
  console.log(context.rule);
};

// Fetch API tokens
(function() {
  var getdata     = { token: data.token };
  if ( data.account ) getdata.account = data.account;

  _.ajax({ 'uri': '/api/v1/mappings?tokenid=' + token.id, data: getdata }, function(response) {
    if(response.status !== 200) return;
    while(response.data.length) {
      var mapping = response.data.shift();
      rv.data.form.rules.push(mapping);
    }
  });
})();
