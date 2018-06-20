window._      = require('../lib/fw');
window._.ajax = require('../lib/ajax');
window.rv     = require('../lib/rivets');

var q      = require('../lib/query'),
    data   = q.decode(window.location.search||'');

// Prepare rv data
rv.data.form.account = { isAdmin: false };
rv.data.form.tokenid = token.id;
rv.data.form.rules   = [];

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

// RV actions
rv.data.form.addrule = function( event, context ) {
  var btn = event.target;
  while(btn.tagName!=='BUTTON') btn = btn.parentNode;

  // Disable the button
  var orgs = [{ el: btn, html: btn.innerHTML, dis: btn.disabled }];
  btn.innerText = 'Creating...';
  btn.disabled  = true;

  // Allow button redraw
  setTimeout(function() {

    // Auth because a mapping contains a 'token' field
    var postdata = {auth:data.token,token:context.form.tokenid};
    if ( data.account ) postdata.account = data.account;

    // Make the call
    _.ajax({
      'method': 'POST',
      'uri'   : '/api/v1/mappings',
      'data'  : postdata
    }, function(response) {
      if(response.status!==200) return revert(orgs);

      // Add to the list
      context.form.rules.push(response.data);
      revert(orgs);
    });
  }, 10);
};
rv.data.form.delrule = function( event, context ) {
  var btn = event.target;
  while(btn.tagName!=='BUTTON') btn = btn.parentNode;
  var dialog = btn.value === 'confirm' ? btn : btn.nextElementSibling;
  while(dialog.tagName !== 'DIALOG') dialog = dialog.parentNode;

  // Confirmed = go ahead
  if ( btn.value === 'confirm' ) {
    var deldata     = { token: data.token };
    if ( data.account ) deldata.account = data.account;

    // Disable all buttons
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
        uri    : '/api/v1/mappings/' + context['rule'].id,
        data   : deldata
      }, function(response) {
        if ( response.status !== 200 ) return revert(orgs);
        var index = context['%rule%'];
        context.form.rules.splice(index,1);
        dialog.close();
      });
    }, 10);
    return false;
  }

  // Show the confirmation dialog
  fancyDialog(dialog);
  dialog.show();
  return false;
};
rv.data.form.saverule = function( event, context ) {
  console.log(context.rule);
};

// Fetch API tokens
(function() {
  var getdata     = { token: data.token, tokenid: token.id };
  if ( data.account ) getdata.account = data.account;

  _.ajax({ 'uri': '/api/v1/mappings', data: getdata }, function(response) {
    if(response.status !== 200) return;
    while(response.data.length) {
      var mapping = response.data.shift();
      rv.data.form.rules.push(mapping);
    }
  });
})();
