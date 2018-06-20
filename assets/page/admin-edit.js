window._      = require('../lib/fw');
window._.ajax = require('../lib/ajax');
window.rv     = require('../lib/rivets');

var q      = require('../lib/query'),
    data   = q.decode(window.location.search||'');

// Prepare rv data
rv.data.form.account = { isAdmin: false };
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

// Fetch our own account
(function() {
  _.ajax({ 'uri': '/api/v1/user/me', data: data }, function(response) {
    if(response.status!==200) return;
    if(!response.data.settings) return;
    rv.data.form.account.username  = response.data.username       || false;
    rv.data.form.account.isAdmin   = response.data.settings.admin || false;
    rv.data.form.account.showToken = response.data.settings.token || false;
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
