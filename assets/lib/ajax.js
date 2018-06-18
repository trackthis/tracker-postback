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

  return function( uri, data, cb ) {
    var req = httpObject();
    if(!req) { return; }
    if ( 'function' === typeof data ) {
      cb   = data;
      data = {};
    }
    if ( 'function' !== typeof cb ) {
      throw new Error("Callback must be a function");
    }
    if (Object.keys(data).length) {
      uri += (uri.indexOf('?')>=0) ? '&' : '?';
      uri += query.encode(data);
    }
    req.open('GET',uri,true);
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
    req.send();
  };
})();
