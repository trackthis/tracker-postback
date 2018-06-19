window._      = require('../lib/fw');
window._.ajax = require('../lib/ajax');
window.rv     = require('../lib/rivets');

var q      = require('../lib/query'),
    data   = q.decode(window.location.search||'');

// Prepare rv data
rv.data.form.account = { isAdmin: false };
rv.data.form.tokenid = token.id;
rv.data.form.rules   = [];

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
  var index = context['%rule%'];
  context.form.rules.splice(index,1);
  // TODO: del through API
};
rv.data.form.saverule = function( event, context ) {
  console.log(context.rule);
};

// Fetch API tokens
(function() {
  var getdata     = { token: data.token };
  if ( data.account ) getdata.account = data.account;

  console.log(getdata);

  _.ajax({ 'uri': '/api/v1/mappings/' + token.id, data: getdata }, function(response) {
    if(response.status !== 200) return;
    while(response.data.length) {
      var mapping = response.data.shift();
      rv.data.form.rules.push(mapping);
    }
  });
})();
