var rv = module.exports = {
  data : { api: {}, action: {} }, lang : {}
};

var rivets                = require('rivets');
rivets.templateDelimiters = ['[[', ']]'];
rv.bindings               = [];
rv.updateDom              = function (arg, resolve, reject) {
  rv.bindings.forEach(function (binding) {
    binding.sync();
  });
  if ('function' === typeof resolve) {
    resolve();
  }
};

// Bind once the current run of code is done
setTimeout(function(){
  rv.bindings = rivets.bind(document.body, rv.data).bindings;
}, 0);
