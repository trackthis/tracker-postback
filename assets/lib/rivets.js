var rv = module.exports = {
  data : { api: {}, action: {}, form: {} }, lang : {}
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

rivets.formatters.prepend = function( origin, arg ) {
  return arg + origin;
};

rivets.formatters.append = function( origin, arg ) {
  return origin + arg;
};

// Bind once the current run of code is done
setTimeout(function(){
  rv.bindings = rivets.bind(document.body, rv.data).bindings;
}, 0);
