/*
 * Polyfill varios
 * https://developer.mozilla.org
 */
if (typeof Object.assign !== 'function') {
  Object.assign = function (target, varArgs) { // .length of function is 2
    'use strict';
    if (target === null) { // TypeError if undefined or null
      throw new TypeError('Cannot convert undefined or null to object');
    }

    var to = Object(target);

    for (var index = 1; index < arguments.length; index++) {
      var nextSource = arguments[index];

      if (nextSource !== null) { // pasamos si es undefined o null
        for (var nextKey in nextSource) {
          // Evita un error cuando 'hasOwnProperty' ha sido sobrescrito
          if (Object.prototype.hasOwnProperty.call(nextSource, nextKey)) {
            to[nextKey] = nextSource[nextKey];
          }
        }
      }
    }
    return to;
  };
}

if (typeof Object.create !== 'function') {
    (function () {
        var F = function () {};
        Object.create = function (o) {
            if (arguments.length > 1) { 
              throw Error('Second argument not supported');
            }
            if (o === null) { 
              throw Error('Cannot set a null [[Prototype]]');
            }
            if (typeof o !== 'object') { 
              throw TypeError('Argument must be an object');
            }
            F.prototype = o;
            return new F();
        };
    })();
}
