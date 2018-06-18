#!/usr/bin/env node

// Load modules
var browserify = require('browserify'),
    co         = require('co'),
    esprima    = require('esprima'),
    fs         = require('fs-extra'),
    path       = require('path'),
    through    = require('through');


// Helper function
function ensureWriteStream(file, callback) {
  if ('function' !== typeof callback) {
    return new Promise(function (resolve, reject) {
      ensureWriteStream(file, function (err, stream) {
        if (err) {
          return reject(err);
        }
        resolve(stream);
      });
    });
  }
  fs.ensureFile(file, function (err) {
    if (err) {
      return callback(err);
    }
    callback(undefined, fs.createWriteStream(file));
  });
}

// Define our own variables
var approot = path.dirname(__dirname),
    srcDir  = path.join(approot, 'assets', 'common'),
    dstDir  = path.join(approot, 'web', 'assets', 'common');

// The transform function
function transform( file, argv ) {
  var Syntax = esprima.Syntax,
      buffer = [];
  return through(write,flush);
  function write(chunk) {
    buffer.push(chunk);
  }
  function flush() {
    var src  = buffer.join(''),
        rep  = [];
    function match(node) {
      return (
        node.type === Syntax.MemberExpression &&
        node.object.type === Syntax.MemberExpression &&
        node.object.computed === false &&
        node.object.object.type === Syntax.Identifier &&
        node.object.object.name === 'process' &&
        node.object.property.type === Syntax.Identifier &&
        node.object.property.name === 'env' &&
        ( node.computed ? node.property.type === Syntax.Literal : node.property.type === Syntax.Identifier )
      );
    }
    esprima.parse(src,{tolerant:true}, function(node,meta) {
      if (match(node)) {
        var key   = node.property.name || node.property.value,
            value = process.env[key];
        if (value !== undefined) {
          rep.push({ node: node, meta: meta, value: JSON.stringify(value) });
        }
      }
    });
    if ( rep.length > 0 ) {
      rep.sort(function(a,b) {
        return b.meta.start.offset - a.meta.start.offset;
      });
      for (var i = 0; i < rep.length; i++) {
        var r = rep[i];
        console.log('@@','-'+r.meta.start.offset+','+(r.meta.end.offset-r.meta.start.offset),'+'+r.meta.start.offset+','+r.value.length,'@@',file);
        console.log('-'+src.slice(r.meta.start.offset,r.meta.end.offset));
        console.log('+'+r.value);
        src = src.slice(0, r.meta.start.offset) + r.value + src.slice(r.meta.end.offset);
      }
    }
    this.queue(src);
    this.queue(null);
  }
}

// Go async
co(function* () {

  // Fetch the files to compile
  var files = (yield fs.readdir(srcDir)).map(function(filename) {
    return path.join(srcDir,filename);
  });

  (function next() {
    co(function*() {
      var filename = files.shift();
      if(!filename) { return; }
      if(filename.slice(-3)!=='.js') { return; }
      console.log('  -', filename.split(path.sep).pop());
      var b      = browserify(filename),
          output = yield ensureWriteStream(path.join(dstDir,filename.split(path.sep).pop()));
      b.transform(transform);
      b.bundle().pipe(output).on('finish', next);
    });
  })();
});
