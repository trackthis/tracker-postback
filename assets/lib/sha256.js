const crypto = require('crypto');

module.exports = function sha256 (src) {
  return crypto.createHash('sha256').update(src).digest();
};