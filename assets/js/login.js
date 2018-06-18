var base64url = require('base64url'),
    crypto    = require('crypto'),
    EC        = require('elliptic').ec;

var $ = (function() {
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

// Browser not having localStorage should be ashamed
var localstorage = (window||{}).localStorage || Object.create({
  getItem    : function(key) { return this[key]; },
  setItem    : function(key,value) { this[key] = value; },
  removeItem : function(key) { delete this[key]; }
});

window.query = {
  decode: function( src ) {
    return src
      .split('&')
      .reduce(function(out,element) {
        var parts     = element.split('='),
            path      = parts.shift().trim().split(']').join('').split('[').map(decodeURIComponent),
            value     = decodeURIComponent(parts.join('=').trim()),
            reference = out, key;
        while(path.length>1) {
          key = path.shift();
          reference=(reference[key]=reference[key]||{})
        }
        reference[path.shift()] = isNaN(value) ? value : parseFloat(value);
        return out;
      }, {})
  },
  encode: function( data, prefix ) {
    prefix = prefix || '';
    var out = '';
    Object.keys(data).forEach(function(key) {
      if ( !key ) return;
      if ( null === data[key] ) return;
      switch(typeof data[key]) {
        case 'undefined': return;
        case 'object':
          out += (out?'&':'')+query.encode(data[key],prefix+(prefix?'[':'')+key+(prefix?']':''));
          break;
        case 'boolean':
        case 'string':
        case 'number':
          if ( out ) out += '&';
          if ( prefix ) out += prefix + '[';
          out += key;
          if ( prefix ) out += ']';
          out += '=';
          if('boolean'===typeof data[key]) {
            out += data[key] ? '1' : '0';
          } else {
            out += data[key];
          }
      }
    });
    return out;
  }
};

function sha256 (src) {
  return crypto.createHash('sha256').update(src).digest();
}

// Generates private key with pbkdf2 (between 1e3 & 1e6 iterations)
function generateSecret(username, password) {
  var _hash  = sha256(username).toString('hex'),
      result = 0;
  while (_hash.length) {
    result = ((result * 16) + parseInt(_hash.substr(0, 1), 16)) % (1e6 - 1e3);
    _hash  = _hash.substr(1);
  }
  return crypto.pbkdf2Sync(password,username,result+1e3,64,'sha256');
}

$('#loginform').each(function(el) {
  $(el).on('submit', function() {

    // Fetch data & buttons
    var name = $('#username').value(),
        pass = $('#password').value(),
        $btn = $(el).find('button');

    // Save button states
    var orgs = $btn.map(function(btn) {
      return { el: btn, dis: btn.disabled, html: btn.innerHTML };
    });

    // Disable all buttons
    $btn.each(function(btn) {
      btn.disabled  = true;
      btn.innerHTML = 'Calculating...';
    });

    // Timeout = allow browser to render
    setTimeout(function() {

      // Generate full KP
      var ec  = new EC('p256'),
          pri = generateSecret(name,pass).toString('hex'),
          kp  = ec.keyFromPrivate(pri);

      // Generate token that's valid for 2 hours
      var header    = { typ: 'JWT', alg: 'ES256', exp: Math.floor((new Date()).getTime()/1000)+7200 },
          claims    = { usr: name },
          unsigned  = base64url.encode(JSON.stringify(header))+'.'+base64url.encode(JSON.stringify(claims)),
          signature = Buffer.from(kp.sign(sha256(unsigned).toString('hex')).toDER('hex'),'hex'),
          signed    = unsigned + '.' + base64url.fromBase64(signature.toString('base64'));

      // Revert all buttons
      orgs.forEach(function(record) {
        record.el.disabled  = record.dis;
        record.el.innerHTML = record.html;
      });

      // Dump public key to console
      // Debug purposes
      console.log('pub:',kp.getPublic('hex'));
      console.log('token:',signed);

      // var publicKey = '-----BEGIN PUBLIC KEY-----\n' + Buffer.from(kp.getPublic('hex'),'hex').toString('base64') + '\n-----END PUBLIC KEY-----';
      // console.log(publicKey);

      // Insert token into GET params
      var q = query.decode((document.location.search||'?').substr(1));
      q.token = signed;
      document.location.search = query.encode(q);
    },10);
  });
});
