// Revert function for common savings
module.exports = function revert(orgs) {
  if(!Array.isArray(orgs)) return;
  orgs.forEach(function(org) {
    if(!org.el) return;
    if ( 'html'  in org ) org.el.innerHTML = org.html;
    if ( 'text'  in org ) org.el.innerText = org.text;
    if ( 'dis'   in org ) org.el.disabled  = org.dis;
    if ( 'class' in org ) org.el.className = org.class;
  });
  return false;
};