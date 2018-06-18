module.exports = {
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
