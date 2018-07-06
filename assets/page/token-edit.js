window._      = require('../lib/fw');
window._.ajax = require('../lib/ajax');
window.rv     = require('../lib/rivets');

const q           = require('../lib/query'),
      data        = q.decode(window.location.search||''),
      revert      = require('../lib/revert'),
      fancyDialog = require('../lib/fancyDialog');

// Prepare rv data
token.account         = token.account || data.account || '';
rv.data.form.account  = { isAdmin: false };
rv.data.form.rules    = [];
rv.data.token         = token;
rv.data.token.account = rv.data.token.account || rv.data.token.username;

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
    var postdata = {
      account : token.username || data.account,
      auth    : data.token,
      token   : token.id
    };

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
        revert(orgs);
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
  var btn = event.target;
  while(btn.tagName!=='BUTTON') btn = btn.parentNode;

  // Disable the button
  var orgs = [{ el: btn, dis: btn.disabled }];
  btn.disabled  = true;

  // Allow button redraw
  setTimeout(function () {

    // Auth because a mapping contains a 'token' field
    var postdata   = context.rule;
    postdata.auth  = data.token;
    postdata.token = token.id;
    if ( data.account ) postdata.account = data.account;

    // Make the call
    _.ajax({
      'method': 'POST',
      'uri'   : '/api/v1/mappings',
      'data'  : postdata
    }, function(response) {
      if(response.status!==200) {
        // TODO: revert fields
        return revert(orgs);
      }
      revert(orgs);
    });
  }, 10);
};

rv.data.form.savetoken = function( event, context ) {
  var btn = this;
  if(btn.tagName!=='BUTTON') return;
  var frm = btn;
  while(frm.tagName!=='FORM') frm = frm.parentNode;
  if(!frm.reportValidity()) return;

  // Disable the button
  var orgs = [{ el: btn, html: btn.innerHTML, dis: btn.disabled }];
  btn.innerText = 'Saving...';
  btn.disabled  = true;

  // Allow button redraw
  setTimeout(function () {

    // Auth because a mapping contains a 'token' field
    var postdata  = Object.assign({},context.token);
    postdata.auth = data.token;
    if ( data.account ) postdata.account = data.account;

    // This allows us to update the description
    if (!rv.data.form.account.isAdmin) {
      delete postdata.expires;
      delete postdata.username;
      delete postdata.target;
    }

    // Make the call
    _.ajax({
      'method' : 'POST',
      'uri'    : '/api/v1/tokens',
      'data'   : postdata
    }, function(response) {
      if(response.status!==200) return revert(orgs);
      var dialog = _('#tokenDialog')[0];
      Object.assign(rv.data.token,response.data);
      revert(orgs);
      if ( response.data.token ) {
        fancyDialog(dialog);
        dialog.show();
      }
    });
  }, 10);
};

rv.data.form.deltoken = function( event, context ) {
  var btn = event.target;
  while(btn.tagName!=='BUTTON') btn = btn.parentNode;

  // Disable the button
  var orgs = [{ el: btn, html: btn.innerHTML, dis: btn.disabled }];
  btn.innerText = 'Deleting...';
  btn.disabled  = true;

  // Allow button redraw
  setTimeout(function () {

    _.ajax({
      'method' : 'DELETE',
      'uri'    : '/api/v1/tokens/' + token.id,
      'data'   : data
    }, function(response) {
      if (response.status!==200) revert(orgs);
      revert(orgs);
      if (response.data) {
        var uri = rv.data.form.account.isAdmin ? '/admin/'+context.token.username : '/tokens';
        uri += '?token='+data.token;
        window.location.href = uri;
      }
    });
  }, 10);
};

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

// Fetch token mappings
(function() {
  if (!token.id) return;
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
