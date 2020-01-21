/* global Grot */

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
   
    var update= function(icon){
            var state = $(icon).data('btn-state')||'normal';
            
            if(state==='animate'){
                Grot(icon).icon('normal');
            }else {
                Grot(icon).icon('animate', {wait:300});
            }
            return state;
    };
    /**
     * Crea un observer sobre un botón para pequeñas animaciones.
     * Ejemplo práctico de un observer
     * @namespace Grot.observable
     * @name Grot.observable.btn   
     * @class                                                                       
     * @example Grot('.submit').observable.btn();
     * @param {Object|string|undefined} selector Objeto 
     * @return {Grot} Una vez creado el observable no requiere más llamadas
     */         
    Grot.observable.btn = function() {
        $.each($(Grot.selector), function( index, value ) {
            //var Observable = Grot.observable.getInstance();//Copiar los atributos de subject a context
            var btn = value.parentNode;//el boton es parent, en este caso el sujeto observado
            Grot().observable('getSubject', btn);//Copiar los atributos de un sujeto 'observable' a btn
            /*
             * "value" es el sujeto a modificar en este caso es un icono fa de font awesome
             * con la clase .submit que le hemos pasado al constructor
             * <button type="submit" class="btn btn-primary" onclick="" >
             * <span class="submit fa fa-save" aria-hidden="true"></span>
             * Enviar</button>
             * 
             * 'value', como sujeto observable tiene una propiedad "update" que el observador
             * lanzará cada vez que se haga click en el.
             */
            value['update'] = update;//observable.notify(subject) dispara update(), aquí definimos la función
            btn.addObserver(value);//añadimos el subject al observer
            btn.onclick = function(){this.notify(value);};//onclick disparamos la función update
        });

        return this;
    }; 
  // check to evaluate whether "namespace" exists in the
  // global namespace - if not, assign window.namespace an
  // object literal    
}( window.Grot = window.Grot || {}));