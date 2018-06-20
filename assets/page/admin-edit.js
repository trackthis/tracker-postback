window._      = require('../lib/fw');
window._.ajax = require('../lib/ajax');
window.rv     = require('../lib/rivets');

var q      = require('../lib/query'),
    data   = q.decode(window.location.search||''),
    EC     = require('elliptic').ec,
    crypto = require('crypto');

function sha256 (src) {
  return crypto.createHash('sha256').update(src).digest();
}

// Prepare rv data
rv.data.account      = {};
rv.data.form.account = account;
rv.data.api.tokens   = [];

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

// Upgrade a dialog after rivets
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

// Update function for the password
rv.data.form.account.passwordUpdate = function( event, context ) {
  var btn = this,
      frm = this;
  while (frm.tagName !== 'FORM') frm = frm.parentNode;
  if (!frm.reportValidity()) return;
  if (btn.disabled) return;

  // Disable button
  var orgs      = [{el : btn, html : btn.innerHTML, dis : btn.disabled}];
  btn.innerText = 'Generating key pair...';
  btn.disabled  = true;

  // Allow button redraw
  setTimeout(function() {

    // Generate full KP
    var ec  = new EC('p256'),
        pri = generateSecret(rv.data.form.account.username, rv.data.form.account.password).toString('hex'),
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
        username : rv.data.form.account.username,
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
        window.location.href = '/admin/' + postdata.username + '?' + q.encode(data);
        revert(orgs);
      });
    }, 10);
  }, 10);
};

// Fetch our own account
(function() {
  _.ajax({ 'uri': '/api/v1/user/me', data: data }, function(response) {
    if (response.status !== 200) return;
    if (!response.data.settings) return;
    Object.assign(rv.data.account, response.data);
  });
})();

// Fetch API tokens
(function() {
  var getdata     = { token: data.token };
  getdata.account = account.username;

  _.ajax({ 'uri': '/api/v1/tokens', data: getdata }, function(response) {
    if(response.status !== 200) return;
    while(response.data.length) {
      var token = response.data.shift();
      rv.data.api.tokens.push(token);
      token.delete = function( event, context ) {
        var dialog = false,
            el     = this;
        if (this.tagName!=='BUTTON') {
          dialog = el.nextElementSibling;
          if(dialog.tagName!=='DIALOG')return;
          fancyDialog(dialog);
          dialog.show();
          return;
        } else {
          dialog = el;
          while(dialog.tagName!=='DIALOG') dialog = dialog.parentNode;
        }

        // Disable buttons
        var orgs = _(dialog).find('button').map(function(btn) {
          var org = { el: btn, html: btn.innerHTML, dis: btn.disabled };
          btn.innerText = 'Deleting...';
          btn.disabled  = true;
          return org;
        });

        // Allow button redraw
        setTimeout(function() {

          // Make the call
          _.ajax({
            method : 'DELETE',
            uri    : '/api/v1/tokens/' + context['token'].id,
            data   : data
          }, function(response) {
            if ( response.status !== 200 ) return revert(orgs);
            var index = context['%token%'];
            context.api.tokens.splice(index,1);
            revert(orgs);
            dialog.close();
          });
        }, 10);


        console.log(this);
        console.log(arguments);
      };
    }
  });
})();
