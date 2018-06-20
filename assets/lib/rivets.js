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

rivets.formatters.length = function( origin ) {
  if ( 'length' in origin ) return parseInt(origin.length);
  return 0;
};

function flen(n,l) {
  return ('0'.repeat(l||2)+n).slice(-(l||2));
}

rivets.formatters.datetime = {
  read: function( value ) {
    value = parseInt(value);
    if(!value) return '';
    var date = (new Date(value * 1000));
    return date.getFullYear()
           + '-' + flen(date.getMonth() + 1)
           + '-' + flen(date.getDate())
           + 'T' + flen(date.getHours())
           + ':' + flen(date.getMinutes())
           + ':' + flen(date.getSeconds());
  },
  publish: function(value) {
    return Date.parse(value) / 1000;
  }
};

// Bind once the current run of code is done
setTimeout(function(){
  rv.bindings = rivets.bind(document.body, rv.data).bindings;
}, 0);
