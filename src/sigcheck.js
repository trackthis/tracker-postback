#!/usr/bin/env node
// TODO
//   Use sockets for performance
//   right now, this is started as program every time
var EC     = require('elliptic').ec,
    hash   = process.argv[2],
    pubkey = process.argv[3],
    sig    = process.argv[4];
var ec = new EC('p256'),
    kp = ec.keyFromPublic(pubkey,'hex');
process.stdout.write(JSON.stringify(kp.verify(hash,sig,kp)));
