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
    /**                                                                                                                                                                                   
     * Lo mismo que load, pero pasa como primer parámetro un formulario
     * del que obtenemos los parámetros para this.load()
     * @public
     * @param {Object|string} f formulario 
     * @param {Object} p parametros
     * @param {Object} run callback 
     * @param {boolean} recall reusar parámetros anteriores si existen
     * @return {Object} methods
     */
    form: function(f, p, run, recall){
        var tmp = {};
            f = (f instanceof jQuery)? f : $(f);
           $.each(f.serializeArray(), function () {
               if (tmp[this.name] !== undefined) {
                   if (!tmp[this.name].push) {
                       tmp[this.name] = [tmp[this.name]];
                   }      
                   tmp[this.name].push(this.value || '');
               } else {
                   tmp[this.name] = this.value || '';
               }
           });
        Object.assign(tmp, p);
        return methods.load.call(this, tmp, run, recall);         
    },
    /**                                                                                                                                                                                   
     * Cargar un div mandando una peticion con parametros
     * El div tiene definido data-path. Opcional data-params
     * @public
     * @param {Object} p parametros
     * @param {Object} run callback 
     * @param {boolean} [recall] reusar parámetros anteriores si existen
     * @return {Object} methods
     */
    load: function(p, run, recall){
        /*
         * @type jQuery context
         */         
        var $this = $(this._selector);
        /*
         * @type Object[] Object data
         */
        var tmp = $this.data(),
        /*
         * @type {boolean} Petición nueva o recordar la anterior
         */
        recall= recall||false,
        /*
         * @type {Object} callback functions
         */
        run= Object.assign({},{success:false,before:false,after:false}, recall? tmp.lastParams||{} : {}, run||{}),
        /*
         * @type {Object} params
         */
        p = Object.assign({}, recall? tmp.lastParams||{} : {} , p||{});
        /*
         * set Object.data antes de post para tenerlo disponible
         */        
        $this.data('lastParams', p);
        $this.data('lastCallback', run);
        
        if(run.before) run.before();
            Grot.spinner.show();

            $.post(tmp.path,
                    p ,
                function(data) {
                    Grot.spinner.hide();
                    if(typeof data.error !== 'undefined'){
                        /*console.log(data.error);*/
                        if(typeof run.error===  'function'){
                            run.error.call($this, data);
                        }else{
                           console.log(data.error);
                        }

                    }else{
                        $this.html(data);
                        if(run.success) run.success();
                    }
                }).done(function() {
                    if(run.after) run.after();
                }
            );
            return Grot;//methods;
    },
    /**                                                                                                                                                                                   
     * Recargar un div con load. Utiliza los argumentos de load
     * @public
     * @function
     */    
    reload: function(){
        let i= 0, args = [];
            for(; i<2; i++){
                args[i]= arguments[i]||undefined;
            }
        args[2]=true;
        return methods.load.apply(this,args);
    }
    };
    
    /**
     * Grot.loader().<br/>
     * Práctico para paginación o carga en div utilizando atributos data-*
     * Combina el uso de un formulario y un div para cargar contenido html (no json).<br>
     * De esta forma, podemos almacenar parámetros concretos en data-params para
     * reutilizar búsquedas, cargar el div tras la primera carga de la página... lo que se nos ocurra.
     *
     * @example:
     * <div id="{{attr.pager.id}}"  // Id para llamar a este div desde cualquier botón.
     * class="grotte-paginable"     // Clase para buscar en el arbol del dom y cargarlo
     * data-path='{{attr.path}}'    // url del controlador
     * data-params=''>              // almacen de parámetros para recargar los resultados del div, por ejemplo con paginación
     * </div>
     *
     * @name Grot.loader                                                                          
     * @example Grot('selector').loader('method', [arguments...])
     * @param {Object|string} method y/o argumentos
     * @return {Object} Grot... 
     */         
    Grot.prototype.loader = function(method) {
		if ( methods[method] ) {//llamada al method, puede haber argumentos
		  return methods[ method ].apply(this , Array.prototype.slice.call( arguments, 1 ));
		} else if ( typeof method === 'object' || ! method ) {//el primer elemento son los argumentos (sin metodo)
		  return methods.load.apply( this , arguments );//aplicamos los arg sobre el elem o lo creamos dinamicamente
		}else{
		  console.log( 'Method ' +  method + ' inexistente en Grot.loader' );
		}
                
	};  
  // check to evaluate whether "namespace" exists in the
  // global namespace - if not, assign window.namespace an
  // object literal    
}( window.Grot = window.Grot || {}));