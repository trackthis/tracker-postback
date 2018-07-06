const crypto = require('crypto'),
      sha256 = require('./sha256');

// Generates private key with pbkdf2 (between 1e3 & 1e3+63317 iterations)
module.exports = function generateSecret(username, password) {
  let _hash  = sha256(username).toString('hex'),
      result = 0;
  while (_hash.length) {
    result = ((result * 16) + parseInt(_hash.substr(0, 1), 16)) % 63317;
    _hash  = _hash.substr(1);
  }
  // console.log('Iterations:', 1e3+result);
  return crypto.pbkdf2Sync(password,username,result+1e3,64,'sha256');
};