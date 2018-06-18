var rv = module.exports = {
  data : { api: {} }, lang : {}
};

var rivets                = require('rivets');
rivets.templateDelimiters = ['[[', ']]'];
rv.bindings               = rivets.bind(document.body, rv.data).bindings;
rv.updateDom              = function (arg, resolve, reject) {
  rv.bindings.forEach(function (binding) {
    binding.sync();
  });
  if ('function' === typeof resolve) {
    resolve();
  }
};
