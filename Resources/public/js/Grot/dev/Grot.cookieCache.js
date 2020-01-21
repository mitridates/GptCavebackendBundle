/**
 * @var {Object} Grot "Top" namespace asignado a un object literal
 */
var Grot = Grot || {};
/**
 * Module Pattern: immediately-invoked function expression : IIFE 
 * namespace (our namespace name) and undefined are passed here
 * to ensure 1. namespace can be modified locally and isn't
 * overwritten outside of our function context
 * 2. the value of undefined is guaranteed as being truly
 * undefined. This is to avoid issues with undefined being
 * mutable pre-ES5.
 * @param {Object} Grot namespace
 * @param {undefined} undefined
 */
(function(Grot, undefined) {
   var methods=
    {
        set: function(key, value) {
                        // Requerimos JSON
                        if(typeof JSON === 'undefined' || !JSON.stringify) {
                                throw 'JSON api is required!';
                        }

                        value = JSON.stringify(value);

                        document.cookie = key+"="+value+"; path=/";
                        return methods;
                },
        get: function(key) {
        // Requerimos JSON
        if(typeof JSON === 'undefined' || !JSON.parse) {
                throw 'JSON api is required!';
        }	

        key += "=";

        var ca = document.cookie.split(';'),
                        c;

        for(var i = 0, l = ca.length; i < l; i++) {
                c = ca[i];

                while (c.charAt(0) === ' ') c = c.substring(1, c.length);

                if (c.indexOf(key) === 0)
                        return JSON.parse(c.substring(key.length, c.length));
        }

        return null;
        }
    };
    
    /**
     * Algunas opciones para Grot.loader().<br/>
     * Práctico para paginación o carga en div utilizando atributos data-*  
     * @name Grot.loader                                                                          
     * @example Grot.loader(selector)[method](arguments)
     * @param {Object|string} selector Objeto 
     * @return {Object} Grot.loader.methods
     */         
    Grot.CookieCache = function(method) {
            if ( methods[method] ) {
              return methods[ method ].apply(this, Array.prototype.slice.call( arguments, 1 ));
            }else{
              console.log( 'Method ' +  method + ' inexistente en Grot.cache' );
            }
    }; 
  // check to evaluate whether "namespace" exists in the
  // global namespace - if not, assign window.namespace an
  // object literal    
}( window.Grot = window.Grot || {}));