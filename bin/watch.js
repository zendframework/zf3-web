// Watch the stat file and remove the cache file
var fs = require('fs');

var args = process.argv.slice(2);

if (args.length !== 2) {
  console.log("Usage: node watch.js <file-to-watch> <file-cache-to-remove>");
  return;
}
fileName = args[0];
fileCache = args[1];

if (!fileExists(fileName)) {
  console.log("Error: the file '" + fileName + "' does not exist");
  return;
}

console.log("Watching file: " + fileName);
fs.watch(fileName, (event, filename) => {
  if (event === 'change') {
    console.log("Delete the cache file: " + fileCache);
    fs.unlink(fileCache);
  }
});

function fileExists(filePath)
{
    try {
        return fs.statSync(filePath).isFile();
    } catch (err) {
        return false;
    }
}
