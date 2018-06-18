var query = require('./query');

module.exports = (function () {
  var factories = [
    /** global: XMLHttpRequest */
    /** global: ActiveXObject */
    function () {return new XMLHttpRequest();},
    function () {return new ActiveXObject("Msxml2.XMLHTTP");},
    function () {return new ActiveXObject("Msxml3.XMLHTTP");},
    function () {return new ActiveXObject("Microsoft.XMLHTTP");}
  ];

  function httpObject() {
    return factories.reduce(function(xmlhttp,factory) {
      try { return xmlhttp || factory(); } catch(e) { return xmlhttp; }
    }, false);
  }

  return function( options, cb ) {
    options = options || {};
    var req = httpObject();
    if(!req) { return; }
    if ( 'function' === typeof options ) {
      cb      = options;
      options = {};
    }
    if ( 'function' !== typeof cb ) {
      throw new Error("Callback must be a function");
    }
    if( 'string' !== typeof options.uri) {
      throw new Error("Uri not given or not a string");
    }
    options.method = (options.method||'GET').toUpperCase();

    if ( (options.method === "GET") && options.data ) {
      options.uri += (options.uri.indexOf('?')>=0) ? '&' : '?';
      options.uri += query.encode(options.data);
      options.data = undefined;
    }

    req.open(options.method,options.uri,true);
    req.onreadystatechange = function() {
      if(req.readyState!==4) { return; }
      var response = {
        status : req.status,
        text   : req.responseText,
        data   : undefined
      };
      try {
        response.data = JSON.parse(response.text);
      } catch(e) {
        response.data = undefined;
      }
      cb(response);
    };
    // JSON encode the data if present
    if ( options.data ) {
      req.setRequestHeader('Content-Type','application/json');
      options.data = JSON.stringify(options.data);
    }
    req.send(options.data);
  };
})();
