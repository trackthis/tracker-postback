window._      = require('../lib/fw');
window._.ajax = require('../lib/ajax');
window.rv     = require('../lib/rivets');

var crypto = require('crypto'),
    EC     = require('elliptic').ec,
    q      = require('../lib/query'),
    data   = q.decode(window.location.search||'');

// Revert function for common savings
function revert(orgs) {
  if(!Array.isArray(orgs)) return;
  orgs.forEach(function(org) {
    if(!org.el) return;
    if ( 'html'  in org ) org.el.innerHTML = org.html;
    if ( 'text'  in org ) org.el.innerText = org.text;
    if ( 'dis'   in org ) org.el.disabled  = org.dis;
    if ( 'class' in org ) org.el.className = org.class;
  });
  return false;
}

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

rv.data.account = account;

rv.data.account.logout = function() {
  window.location.href='/';
};

rv.data.account.passwordUpdate = function() {
  var btn = this;
  if(btn.tagName!=='BUTTON') return;
  var frm = btn;
  while(frm.tagName!=='FORM') frm = frm.parentNode;
  if(!frm.reportValidity()) return;

  // Disable button
  var orgs = [{ el: btn, html: btn.innerHTML, dis: btn.disabled }];
  btn.innerText = 'Generating key pair...';
  btn.disabled  = true;

  // Allow button redraw
  setTimeout(function() {

    // Generate full KP
    var ec  = new EC('p256'),
        pri = generateSecret(rv.data.account.username, rv.data.account.password).toString('hex'),
        kp  = ec.keyFromPrivate(pri);

    // Change button text
    orgs.forEach(function(record) {
      record.el.innerHTML = 'Updating user...';
    });

    // Allow button redraw
    setTimeout(function() {

      // Create post data
      var postdata = {
        token    : data.token||'',
        username : rv.data.account.username,
        pubkey   : kp.getPublic('hex')
      };

      // Submit what we just did
      _.ajax({
        method : 'POST',
        uri    : "/api/v1/accounts",
        data   : postdata
      }, function(response) {
        if (response.status!==200) {
          console.log('TODO: error handling');
          return;
        }
        window.location.reload(true);
        // revert(orgs);
      });
    }, 10);
  }, 10);
};
