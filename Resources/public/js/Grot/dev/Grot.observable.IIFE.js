// //https://bumbu.github.io/javascript-observer-publish-subscribe-pattern/      
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
    function ObserverList(){
      this.observerList = [];
    }

    ObserverList.prototype.add = function( obj ){
      return this.observerList.push( obj );
    };

    ObserverList.prototype.count = function(){
      return this.observerList.length;
    };

    ObserverList.prototype.get = function( index ){
      if( index > -1 && index < this.observerList.length ){
        return this.observerList[ index ];
      }
    };

    ObserverList.prototype.indexOf = function( obj, startIndex ){
      var i = startIndex;

      while( i < this.observerList.length ){
        if( this.observerList[i] === obj ){
          return i;
        }
        i++;
      }

      return -1;
    };

    ObserverList.prototype.removeAt = function( index ){
      this.observerList.splice( index, 1 );
    };

    function Subject(){
      this.observers = new ObserverList();
    }

    Subject.prototype.addObserver = function( observer ){
      this.observers.add( observer );
    };

    Subject.prototype.removeObserver = function( observer ){
      this.observers.removeAt( this.observers.indexOf( observer, 0 ) );
    };

    Subject.prototype.notify = function( context ){
      var observerCount = this.observers.count();
      for(var i=0; i < observerCount; i++){
        this.observers.get(i).update( context );
        
      }
    };        
    var methods = {
        /**                                                                                                                                                                                   
         * Elemento observable
         * @function
         * @param {string|undefined} obj merge obj con propiedades subject
         * @return {Subject} Objeto a observar
         */           
        getSubject: function(obj){
            var instance = new Subject();
            var obj = arguments[0] || {};
            for ( var key in instance ){
              if(typeof obj[key]=== 'undefined') obj[key] = instance[key];
            }        
            return obj;
        }        
    };

   /**                                                                                                                                                                                   
    * Observers!
    * @name Grot.observable
    * @function
    * @param {string|undefined} method - Llamada a alguno de los metodos existentes
    * @return {*} methods|array... Depende si tiene sentido el encadenamiento o es una función para obtener datos
    */         
    Grot.observable = function(method) {
		if ( methods[method] ) {
		  return methods[ method ].apply($(Grot.selector) , Array.prototype.slice.call( arguments, 1 ));
		}else if ( typeof method === 'object' || ! method ) {
		  return methods.getSubject.apply( this, arguments );
		}else{
		  console.log( 'Method ' +  method + ' inexistente en Grot.observable' );
		}
	};   

  // check to evaluate whether "namespace" exists in the
  // global namespace - if not, assign window.namespace an
  // object literal    
}( window.Grot = window.Grot || {}));

/*
 * Crear una clase observer
 */

//(function(Grot, undefined) {
//   
//    var update= function(icon){
//            var state = $(icon).data('btn-state')||'normal';
//            if(state==='animate'){
//                Grot(icon).icon('normal');
//            }else {
//                Grot(icon).icon('animate', {wait:300});
//            }
//            return state;
//    };
//    /**
//     * Crea un observer sobre un botón para pequeñas animaciones.
//     * Ejemplo práctico de un observer
//     * @namespace Grot.observable
//     * @name Grot.observable.btn   
//     * @class                                                                       
//     * @example Grot('.submit').observable.btn();
//     * @param {Object|string|undefined} selector Objeto 
//     * @return {Grot} Una vez creado el observable no requiere más llamadas
//     */         
//    Grot.observable.btn = function(selector) {
//        $.each($(Grot.selector), function( index, value ) {
//            //var Observable = Grot.observable.getInstance();//Copiar los atributos de subject a context
//            var btn = value.parentNode;//el boton es parent, en este caso el sujeto observado
//            Grot().observable('getSubject', btn);//Copiar los atributos de un sujeto 'observable' a btn
//            /*
//             * "value" es el sujeto a modificar en este caso es un icono
//             * con la clase .submit que le hemos pasado al constructor
//             * 
//             * 'value', como sujeto observable tiene una propiedad "update" que el observador
//             * lanzará cada vez que se haga click en el.
//             */
//            value['update'] = update;//observable.notify(subject) dispara update(), aquí definimos la función
//            btn.addObserver(value);//añadimos el subject al observer
//            btn.onclick = function(){this.notify(value);};//onclick disparamos la función update
//        });
//
//        return this;
//    };
//}( window.Grot = window.Grot || {}));  

/*
 * Llamar a la clase desde la página
 */
//<script type="text/javascript">
//Grot('.submit').observable.btn()//$(document).ready?
//</script>
/*
 * Agregar los botones que haga falta
 */
// <button type="submit" class="btn btn-primary" onclick="" >
// <span class="submit fa fa-save" aria-hidden="true"></span>
// Enviar</button>