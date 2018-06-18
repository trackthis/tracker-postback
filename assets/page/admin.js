window.$      = require('../lib/fw');
window.$.ajax = require('../lib/ajax');
window.rv     = require('../lib/rivets');

var q    = require('../lib/query'),
    data = q.decode(window.location.search||'');

console.log(data);
