window.inputmatch = function(el, id) {
  function esc(str) { return str.replace(/[\-\[\]\/\{\}\(\)\*\+\?\.\\\^\$\|]/g, "\\$&"); }
  document.getElementById(id).pattern = esc(el.value);
};
