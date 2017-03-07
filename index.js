const t = require('babel-types');

const { parse } = require('babylon');
const { readFile } = require('fs');
const { default: traverse } = require('babel-traverse');

const chunks = [];
const stdin = process.stdin;
stdin.setEncoding('utf8');
stdin.on('data', chunk => chunks.push(chunk));
stdin.on('end', () => {
  const files = chunks.join('').trim().split('\n');

  if (files.length === 0) {
    return;
  }

  Promise.all(
    files.map(
      file => new Promise((resolve, reject) => {
        readFile(file, 'utf-8', (error, content) => {
          if (error) {
            console.error(
              `Could not read file "${file}" due to error, skipping`
            );
            return resolve(null);
          }

          try {
            const keys = findTranslationKeys(content);
            if (keys.length > 0) {
              resolve({
                file,
                keys
              });
            } else {
              resolve(null);
            }
          } catch (error) {
            console.error(
              `Could not parse file "${file}" due to error, skipping`
            );
            return resolve(null);
          }
        });
      })
    )
  ).then(translations => {
    console.log(
      JSON.stringify(translations.filter(translation => !!translation), null, 2)
    );
  });
});

function findTranslationKeys(code) {
  const keys = [];
  traverse(parse(code), {
    CallExpression(path) {
      if (isTranslationCall(path)) {
        const key = path.get('arguments')[0];
        if (key.isStringLiteral()) {
          const { loc, value } = key.node;
          keys.push({ loc, value });
        }
      }
    }
  });

  return keys;
}

function isTranslationCall(path) {
  const callee = path.get('callee');
  return callee.isMemberExpression() &&
    callee.get('object').isIdentifier({ name: 'Lang' }) &&
    callee.get('property').isIdentifier({ name: 'get' });
}

module.exports = findTranslationKeys;
