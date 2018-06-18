window._      = require('../lib/fw');
window._.ajax = require('../lib/ajax');
window.rv     = require('../lib/rivets');

var crypto = require('crypto'),
    EC     = require('elliptic').ec,
    q      = require('../lib/query'),
    data   = q.decode(window.location.search||'');

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

function fancyDialog(target) {
  if(Array.isArray(target)) return target.map(fancyDialog);
  if(target.getAttribute('upgraded')) return true;
  target.setAttribute('upgraded',1);
  var cover = document.createElement('DIV');
  // var close = document.createElement('I');
  cover.className = 'cover';
  // close.className = 'close material-icons';
  // close.innerHTML = 'close';
  target.parentNode.insertBefore(cover,target);
  target.parentNode.insertBefore(target,cover);
  // target.appendChild(close);
  _(target).find('.close').on('click', function() {
    target.close();
  });
  return true;
}

function accountDelete( event, context ) {
  var el = this;
  if (this.tagName!=='BUTTON') {
    _(el.parentNode).find('dialog').each(function(dialog) {
      fancyDialog(dialog);
      dialog.show();
    });
    return;
  }

  _.ajax({
    method : 'DELETE',
    uri    : "/api/v1/accounts/" + context.account.username,
    data   : data
  }, function(response) {
    if (response.status === 200) {
      document.location.reload(true);
    }
  });
}

function accountEdit( event, context ) {
  var args = [].slice.call(arguments);
  console.log(this, args);
}

rv.data.form.account = { isAdmin: false };
rv.data.api.accounts = [];
_.ajax({ 'uri': '/api/v1/accounts', data: data }, function(response) {
  if(response.status !== 200) return;
  while(response.data.length) {
    var acc    = response.data.shift();
    acc.delete = accountDelete;
    acc.edit   = accountEdit;
    rv.data.api.accounts.push(acc);
  }
});

rv.data.action.newAccountDialog = function( event, context ) {
  var dialog = this.nextElementSibling;
  fancyDialog(dialog);
  dialog.show();
};

// Handle the new account form submit
_("#accountform").on('submit', function() {

  // Fetch form data
  var formdata = {};
  _(this).find('input,select,textarea').each(function(el) {
    formdata[el.name||el.id] = _(el).value();
  });

  // Save button states
  var _btn = _(this).find('button');
  var orgs = _btn.map(function(btn) {
    return { el: btn, dis: btn.disabled, html: btn.innerHTML };
  });

  // Disable all buttons
  _btn.each(function(btn) {
    btn.disabled  = true;
    btn.innerHTML = 'Generating key pair...';
  });

  // Allow button redraw
  setTimeout(function() {

    // Generate full KP
    var ec  = new EC('p256'),
        pri = generateSecret(formdata.username,formdata.password).toString('hex'),
        kp  = ec.keyFromPrivate(pri);

    // Change button text
    orgs.forEach(function(record) {
      record.el.innerHTML = 'Creating user...';
    });

    // Allow button redraw
    setTimeout(function() {

      // Create post data
      var postdata = {
        token    : data.token||'',
        username : formdata.username,
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
        window.location.href = '/admin/' + formdata.username + '?' + q.encode(data);

        // Revert all buttons (just in case)
        orgs.forEach(function(record) {
          record.el.disabled  = record.dis;
          record.el.innerHTML = record.html;
        });
      });
    },10);
  }, 10);
});
