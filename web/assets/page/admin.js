(function(){function r(e,n,t){function o(i,f){if(!n[i]){if(!e[i]){var c="function"==typeof require&&require;if(!f&&c)return c(i,!0);if(u)return u(i,!0);var a=new Error("Cannot find module '"+i+"'");throw a.code="MODULE_NOT_FOUND",a}var p=n[i]={exports:{}};e[i][0].call(p.exports,function(r){var n=e[i][1][r];return o(n||r)},p,p.exports,r,e,n,t)}return n[i].exports}for(var u="function"==typeof require&&require,i=0;i<t.length;i++)o(t[i]);return o}return r})()({1:[function(require,module,exports){
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

  return function( uri, cb ) {
    var req = httpObject();
    if(!req) { return; }
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

},{}],2:[function(require,module,exports){
module.exports = (function() {
  function rdy( cb ) {
    if ( /loaded|complete/.test(document.readyState) ) { cb(); }
    else { setTimeout(rdy.bind(undefined,cb),5); }
  }
  function convert(subject) {
    var out = Object.create(f.fn);
    out.push.apply(out,(subject.__proto__.forEach||subject.__proto__.each) ? subject : [subject]);
    return out;
  }
  var f = function(subject){switch(typeof subject){
    case 'string'  : return convert(document.querySelectorAll(subject));
    case 'object'  : return convert(subject);
    case 'function': return rdy(subject);
    default        : throw new Error('Unknown selector type: ' + (typeof subject));
  }};
  f.fn = {
    find    : function(s) {
      var o=Object.create(f.fn); this.each(function(e) {
        if(!e.querySelectorAll) return;
        o.push.apply(o,[].slice.call(e.querySelectorAll(s)));
      }); return o;
    },
    each    : Array.prototype.forEach,
    length  : 0,
    pop     : Array.prototype.pop,
    push    : Array.prototype.push,
    shift   : Array.prototype.shift,
    unshift : Array.prototype.unshift,
    reduce  : Array.prototype.reduce,
    map     : Array.prototype.map,
    text    : function( newval ) {
      return this.reduce(function(acc,el) {
        if ('undefined'!==typeof newval) el.innerText = newval;
        return el.innerText || acc;
      }, '');
    },
    html    : function( newval ) {
      return this.reduce(function(acc,el) {
        if ('undefined'!==typeof newval) el.innerHTML = newval;
        return el.innerHTML || acc;
      }, '');
    },
    value   : function( newval ) {
      return this.reduce(function(acc,el) {
        if ('undefined'!==typeof newval) el.value = newval;
        return el.value;
      },false);
    },
    on      : function(ev,cb) {
      this.each(function(el) {
        if(el.addEventListener) {
          el.addEventListener(ev,cb,false);
        } else if ( el.attachEvent ) {
          el.attachEvent('on'+ev,function() {
            return cb.call(el,window.event);
          });
        }
      });
      return this;
    },
    emit : function( ev ) {
      this.each(function(el) {
        if ( document.createEvent ) {
          el.fireEvent( 'on' + ev, document.createEventObject());
        } else {
          var e = document.createEvent('HTMLEvents');
          e.initEvent(ev,true,true);
          el.dispatchEvent(e);
        }
      });
      return this;
    }
  };
  return f;
})();

},{}],3:[function(require,module,exports){
window.$      = require('../lib/fw');
window.$.ajax = require('../lib/ajax');

},{"../lib/ajax":1,"../lib/fw":2}]},{},[3]);
