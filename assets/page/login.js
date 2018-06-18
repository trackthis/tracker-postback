var _         = require('../lib/fw'),
    base64url = require('base64url'),
    crypto    = require('crypto'),
    EC        = require('elliptic').ec,
    query     = require('../lib/query');

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

_('#loginform').each(function(el) {
  _(el).on('submit', function() {

    // Fetch data & buttons
    var name = _('#username').value(),
        pass = _('#password').value(),
        _btn = _(el).find('button');

    // Save button states
    var orgs = _btn.map(function(btn) {
      return { el: btn, dis: btn.disabled, html: btn.innerHTML };
    });

    // Disable all buttons
    _btn.each(function(btn) {
      btn.disabled  = true;
      btn.innerHTML = 'Generating key pair...';
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
