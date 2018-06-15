global.Promise = Promise || require('bluebird');

var co   = require('co'),
    exec = require('child_process'),
    fs   = require('fs-extra'),
    path = require('path');

fs.scandir = co.wrap(function* (dir) {
  var stat, filename, i, src = yield fs.readdir(dir);
  var output                 = [];
  for (i in src) {
    if (!src.hasOwnProperty(i)) continue;
    filename = path.join(dir, src[i]);
    stat     = yield fs.stat(filename);
    if (stat.isDirectory()) {
      output = output.concat(yield fs.scandir(filename));
    } else if (stat.isFile()) {
      output.push(filename);
    }
  }
  return output;
});

co(function*() {

  // Fetch files to load
  var files = (yield fs.scandir( __dirname )).filter(function(filename) {
    return (filename !== __filename);
  }).sort();

  var queue = Promise.resolve();

  files
    .forEach(function(filename) {
      queue = queue.then(function() {
        console.log(filename);
        return new Promise(function(resolve,reject) {

          // Run the file itself by default
          var args = [
            filename,
            [],
            { cwd: __dirname }
          ];

          // Some extensions might need to be called differently
          switch(filename.split('.').pop()) {
            case 'js':
              args[0] = 'node';
              args[1] = [filename];
              break;
            case 'sh':
              args[0] = 'bash';
              args[1] = [filename];
              break;
          }

          var child = exec.spawn.apply(null,args);
          child.stdout.on('data', function(data) {
            process.stdout.write(data);
          });
          child.stderr.on('data', function(data) {
            process.stderr.write(data);
          });
          child.on('close', function(code) {
            if(code) reject(code);
            resolve(code);
          });
        });
      });
    });

  return queue;
});
