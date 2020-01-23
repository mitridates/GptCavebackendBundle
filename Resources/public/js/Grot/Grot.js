/**
 * @fileOverview Constructor Grot
 * @author <a href="mailto:mitridates@gmail.com">Mitridates</a>
 * @version 1.0
 */

/**
 *
 * @class Clase para gestional el backend
 * @constructor
 * @name Grot
 * @param {*} selector
 */
 function Grot (selector) {
    if (!(this instanceof Grot)) return new Grot(selector)
    this.setSelector(selector);
}
/**
 * Establece el selector actual
 * @name Grot#setSelector
 * @method
 * @param {*} selector
 * @returns $this
 */
Grot.prototype.setSelector = function(selector){
    /**
     * @default ''
     */
    this._selector = selector || '';
    return this;
};

/**
 * Immediately-Invoked Function Expression (IIFE).
 * @function
 * @param {Object} Grot - Global window object.
 * @returns {Object} window.Grot.cache
 */
(function() {
    /**
     * No exponemos variables
     * @var {Object} _data
     */   
    var _data = {};
    /**
     * Cache global para la página actual. IIEF para no exponer variables de forma global.
     * @name Grot.cache
     * @function
     */
    Grot.cache = {
        /**
         * Get cache key
         * @name Grot.cache#get
         * @param {string} key
         */
        get: function(key) {
        return _data.hasOwnProperty(key)? _data[key] : null ;
        },
        /**
         * @param {string} key
         * @param {*} value
         */
        add: function(key, value) {
            var data =  _data.hasOwnProperty(key)? _data[key] : [] ;
            if(data instanceof Array){
                data.push(value);
                return this.set(key, data);
            }else{
                console.log('Tipo de dato equivocado '+(typeof data)+', se esperaba "Array"');
            }
        },
        /**
         * @param {string} key
         * @param {*} value
         */
        set: function(key, value) {
         _data[key] = value;
         return this;//chaining
        },
        /**
         * @param {string} key
         */
        unset: function(key){
            if(_data.indexOf(key)!==-1) delete _data[key];
            else   console.log('Index "'+ key +'" no definido en cache');
            return this;
        }  
    };
})(window.Grot = window.Grot || {});

/*
 * Un spinner básico para los callback
 */
(function() {
    /*
     * Creamos el Object o lo recuperamos
     * @var {string} style.display prop
     */   
    let _spinner = function(disp){
        let spinElement = document.getElementById('grot-spinner');
        if(spinElement === null){
            spinElement = document.createElement('i');
            spinElement.setAttribute('id', 'grot-spinner');
            spinElement.setAttribute('class', 'fa fa-cog fa-spin fa-2x fa-fw centerbox-xy');
            document.body.appendChild(spinElement);
        }else{
            spinElement=  document.getElementById('grot-spinner');
        }
        spinElement.style.display= disp;
    };
    
    Grot.spinner = {
        show: function() {
         _spinner('inline');
        },
        hide: function() {
         _spinner('none');
     }
    };
    
})(window.Grot = window.Grot || {});

/*
  * Grot(constructor arguments).object([private context method], [arguments...])
 */
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
//(function(Grot, undefined) {
//   /**
//    * Nueva función
//    * @name Grot.name
//    * @function
//    * @param {string|undefined} method - Llamada a alguno de los metodos existentes
//    * @return {*} methods|array... Depende si tiene sentido el encadenamiento o es una función para obtener datos
//    */
//    Grot.prototype.name = function(method) {
//		if ( methods[method] ) {
//		  return methods[ method ].apply(this , Array.prototype.slice.call( arguments, 1 ));
//		}else if ( typeof method === 'object' || ! method ) {
//		  return methods.init.apply( this, arguments );
//		}else{
//		  console.log( 'Method ' +  method + ' inexistente en Grot.name' );
//		}
//	};
  // check to evaluate whether "namespace" exists in the
  // global namespace - if not, assign window.namespace an
  // object literal
//}( window.Grot = window.Grot || {}));


/*
 * Grot.object.publicmethod([arguments...])
 */
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
//(function(Grot, undefined) {
//   /**
//    * Nueva función
//    * @name Grot.name
//    * @function
//    * @param {string|undefined} method - Llamada a alguno de los metodos existentes
//    * @return {*} methods|array... Depende si tiene sentido el encadenamiento o es una función para obtener datos
//    */
//    Grot.name = {
//          key: function(args){}
//          ...
//	};
  // check to evaluate whether "namespace" exists in the
  // global namespace - if not, assign window.namespace an
  // object literal
//}( window.Grot = window.Grot || {}));