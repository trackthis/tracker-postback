// Browser not having localStorage should be ashamed
module.exports = (window||{}).localStorage || Object.create({
  getItem    : function(key)       { return this[key] ; },
  setItem    : function(key,value) { this[key] = value; },
  removeItem : function(key)       { delete this[key] ; }
});
