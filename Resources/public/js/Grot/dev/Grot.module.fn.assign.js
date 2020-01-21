
/**
 * Añadimos una nueva definición de submodulo
 * Este Polyfill sustituye a $.extend(), con diferencia el más utilizado
 */
Grot.module.append('fn', {
    /**
     * Polyfill Object.assign
     * @example Object.assign(objetivo, ...fuentes)
     * @param {Object} target objeto destino.
     * @param {Object} varArgs objetos para asignar.
     * @return {Object} suma de varArgs y target sobre target
     */
    assign: function(target, varArgs){
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
        return Object.assign.apply(this, arguments);
    }
});