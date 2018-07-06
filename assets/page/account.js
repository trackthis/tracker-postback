window._      = require('../lib/fw');
window._.ajax = require('../lib/ajax');
window.rv     = require('../lib/rivets');

const EC             = require('elliptic').ec,
      q              = require('../lib/query'),
      data           = q.decode(window.location.search||''),
      generateSecret = require('../lib/generateSecret'),
      revert         = require('../lib/revert');

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
