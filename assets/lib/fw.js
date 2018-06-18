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

  f.fn.filter = function( callback ) {
    var self = this,
        out  = [];
    Object.keys(self).forEach(function(key) {
      if ( Object.keys(f.fn).indexOf(key) >= 0 ) return;
      if ( callback.call(self[key], self[key]) ) out.push(self[key]);
    });
    return convert(out);
  };

  f.fn.style = function ( parameters ) {
    var args = Array.prototype.slice.call(arguments),
        obj  = this;

    if ( ('object' !== typeof parameters) && args.length === 2 ) {
      var opts = {};
      opts[args[0]] = args[1];
      return f.fn.style.call(this,opts);
    }

    obj.each(function(el) {
      Object.keys(parameters).forEach(function(param) {
        el.style[param] = parameters[param];
      });
    });

    return obj;
  };

  return f;
})();
