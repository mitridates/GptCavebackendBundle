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
    /**          
     * almacena submódulos (métodos dentro de una variable privada)
     * que pueden ser añadidos o llamados
     * @type {Object[]} par nombre método   
     */      
    var modules = {};

    /**          
     * Objeto con métodos para operar con modules
     * @namespace module
     * @type {Object}
     */            
    Grot.module = {
        /**
         * Crear modulo y creditos 
         * @public
         * @argument {string} name nombre modulo
         * @argument {Object[]} credit par {nombre: cadena}. Ex. {version: '1.0'}
         * @argument {Object[]} methods par {nombre: función} para ser pasados a append
         * @return {Grot.module}
         */         
        create: function(name, credit,  methods) {
            if(modules[name] === undefined) {
                modules[name] = {};
            }

            modules[name].credit = function(){
                return credit;
            };
            
            if(methods !== undefined ){
                return Grot.module.append(name, methods);//chainable
            }
            return Grot.module;//chainable
        },
        /**
         * Agregar funciones al módulo
         * @public
         * @argument {string} name modulo
         * @argument {Object[]} module Json par {nombre: funcion}
         * @return {Grot.module}
         */ 
        append: function(name, module) {
            if(modules[name] === undefined) {
                throw 'Module not exists';
            }

            for(var k in module) {
                if(modules[name][k] === undefined) {
                    modules[name][k] = module[k];
                }
            }
            return Grot.module;//chainable
        },
        /**
         * Contiene el módulo?
         * @public
         * @param {string} name
         * @return {bool}
         */         
        has: function(name) {
            return (modules[name] === undefined) ? false : true;
        },    
        /**
         * Obtener un submodulo
         * @public
         * @param {string} name
         * @return {Object[]} modulo
         */          
        get: function(name) {
            if(modules[name] === undefined) {
                throw 'Modulo '+name+' inexistente';
            }
            return modules[name];
        }
        
    };
    /**
     * Añadimos una nueva definición de submodulo
     * Los ficheros con funciones para esta clave están bajo Grot.module.fn.name.js
     */   
    Grot.module.create('mod', {
        name: 'Function modules',
        description: 'Funciones que retornan un valor, objecto o instancia de función para uso ocasional',
        version: '1.0'
    });   
    
    /**          
     * shorthand para obtener funciones añadidas en modules[]. Los argumentos son opcionales.
     * @type {function}
     * @name mod
     * @param {string} name mod name
     * @example Grot.fn('assign', {...}, {...}, {...});
     */       
    Grot.mod= function(name){
        var module = Grot.module.get(name);
        if (typeof module === "function") {
            return module[name].apply( this, Array.prototype.slice.call( arguments, 1 )); 
        }else{
         return module;   
        }
    }; 
    
  // check to evaluate whether "namespace" exists in the
  // global namespace - if not, assign window.namespace an
  // object literal    
}( window.Grot = window.Grot || {}));
