window._      = require('../lib/fw');
window._.ajax = require('../lib/ajax');
window.rv     = require('../lib/rivets');

var q      = require('../lib/query'),
    data   = q.decode(window.location.search||'');

rv.data.account = { isAdmin: false };
rv.data.tokens  = [];

// Fetch me
_.ajax({ 'uri': '/api/v1/user/me', data: data }, function(response) {
  if (response.status !== 200) return;
  if (!response.data.settings) return;
  Object.assign(rv.data.account, response.data);
});

// Fetch my tokens
_.ajax({ 'uri': '/api/v1/tokens', data: data }, function(response) {
  if (response.status !== 200) return;
  [].push.apply(rv.data.tokens,response.data);
  rv.updateDom();
});
