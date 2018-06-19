window._      = require('../lib/fw');
window._.ajax = require('../lib/ajax');
window.rv     = require('../lib/rivets');

var q      = require('../lib/query'),
    data   = q.decode(window.location.search||'');

// Prepare rv data
rv.data.form.account = { isAdmin: false };
rv.data.api.tokens   = [];

// Fetch API tokens
(function() {
  _.ajax({ 'uri': '/api/v1/tokens/'+account.username, data: data }, function(response) {
    if(response.status !== 200) return;
    while(response.data.length) {
      var token  = response.data.shift();
      rv.data.api.tokens.push(token);
    }
  });
})();
