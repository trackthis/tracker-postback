window._      = require('../lib/fw');
window._.ajax = require('../lib/ajax');
window.rv     = require('../lib/rivets');

var q      = require('../lib/query'),
    data   = q.decode(window.location.search||'');

rv.data.form.account = { isAdmin: false };
rv.data.api.tokens = [];
_.ajax({ 'uri': '/api/v1/tokens', data: data }, function(response) {
  if(response.status !== 200) return;
  while(response.data.length) {
    var token  = response.data.shift();
    console.log(token);
    rv.data.api.tokens.push(token);
  }
});
