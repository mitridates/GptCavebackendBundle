/**
 * @let {Object} Grot "Top" namespace asignado a un object literal
 */
let Grot = Grot || {};
/**
 * Module Pattern: immediately-invoked function expression : IIFE 
 * namespace (our namespace name) and undefined are passed here
 * to ensure 1. namespace can be modified locally and isn't
 * overwritten outside of our function selector
 * 2. the value of undefined is guaranteed as being truly
 * undefined. This is to avoid issues with undefined being
 * mutable pre-ES5.
 * @param {Object} Grot namespace
 * @param {undefined} undefined
 */
(function(Grot, undefined) {
    /**
     * Configuración y opciones por defecto
     * @namespace defaults
     */
    let defaults = {
    /**
     * Objeto que contiene los estados y sus correspondientes estilos
     * @memberof defaults
     * @property {Object[]}
     */
        css: {
        /**
         * Css estado animate.
         * @memberOf defaults
         * @default fa-cog gly-spin gly-state-animate
         * @type string
         */                   
            animate: 'fa-sync fa-spin gly-state-animate',
        /**
         * Css estado success.
         * @memberOf defaults
         * @default fa-check gly-state-success
         * @type string
         */             
            success: 'fa-check gly-state-success',
        /**
         * Css estado normal.
         * @memberOf defaults
         * @default fa-save gly-state-normal
         * @type string
         */             
            normal: 'fa-save gly-state-normal',
        /**
         * Css estado exclamation.
         * @memberOf defaults
         * @default fa-exclamation gly-state-exclamation
         * @type string
         */             
            exclamation: 'fa-exclamation gly-state-exclamation',
        /**
         * Css estado danger.
         * @memberOf defaults
         * @default bg-danger gly-state-danger
         * @type string
         */             
            danger: 'bg-danger gly-state-danger'
        },
    /**
     * Valor del objeto literal defaults.css[...] que define el estado inicial.
     * @memberof defaults
     * @default normal
     * @type string
     */
        state: 'normal',
    /**
     * Propiedades a eliminar y volver al estado 'normal'.
     * @memberof defaults
     * @default /fa-.+|gly-.+|bg-.+/
     * @type string
     */        
        regex:  'fa\-|gly\-|bg\-',
    /**
     * setTimeout si se desea.
     * @memberof defaults
     * @default false
     * @type number|boolean
     */        
        wait: 0
    },    
    /**
     * Callback para la función jQuery.attr('class', callback). Elimina las clases segun regex
     * @param {int} i index
     * @param {string} c Clases del objeto
     * @return {string} clases reemplazadas
     */
    _rmcss = function(c){
        let res = c.split(" ");
        let out = [];
        let pattern = new RegExp(defaults.regex);
        for(let i in res){
            //console.log(res[i] + defaults.regex.test(res[i]));
            //console.log('-------');
            if(pattern.test(res[i])){
                 res.splice(i,1);
                
            }else{
                out.push(res[i]);
            }
        }
      return out.join(' ');
    },
    /**
     * Modifica el attr class del objeto eliminando las clases prefijadas
     * @param {int} o opciones para sobreescribir defaults
     * @return {Object} selectoro
     */    
    _set = function(o){
        o = o || {};
        let $this= this;
        let btnData = $this.data();
        let css = $this.attr('class');
        let cssclear = _rmcss(css);//eliminamos clases con prefijos
        o = Object.assign(defaults, $this.data('btn') || {}, o);//assign deep
        let cssnormal = cssclear + ' ' +(btnData.normal||o.css.normal);
        let endcss = cssclear + ' '+o.css[o.state];

        
        if(btnData.state==='waiting'){
            
        }
        $this.attr('class', endcss);//nuevas clases
        $this.data('btn-state', o.state);//modificamos data-btn: {state} con el actual estado
        if(o.wait){//setTimeout para volver a estado normal
            $this.data('btn-state', 'waiting');//modificamos data-btn: {state} con el actual estado
            setTimeout(function(){ 
                $this.attr('class', cssnormal).data('btn-state','normal');//Volver a nornal
            }
                , o.wait 
            );
        }
        //selector para encadenamiento
        return methods;        
    },
    /**
     * Genera o comprueba que el atributo 'normal' de 
     * data-btn: {state:{normal: ...}} existe, permitiendo regresar al estado
     * inicial.
     * @type function
     */     
    _normalize = function(){
        let d = this.data()||{};
        if(typeof d['btn'] === 'undefined'){
            this.data('btn-state', 'normal').data('btn', this.attr('class'));
        }
    },
    _wait=function(){
        return this.data('btn-state')==='waiting';
    },
    /**
     * Metodos públicos disponibles para esta clase
     * @namespace methods
     * @type {{normal: function, success: function, exclamation: function, state: function, animate: function}} methods Metodos publicos
     */      
    methods=
    {
        /**
         * Cambia los estilos al estado predefinido en defaults.css.normal
         * @type function
         * @method normal
         * @memberof methods
         * @argument {Object[]|undefined} o options
         * @returns {methods} 
         */         
        normal: function(o){defaults.state = 'normal'; return methods.state(o);},
        /**
         * Cambia los estilos al estado predefinido en defaults.css.exclamation
         * @type function
         * @method exclamation
         * @memberof methods
         * @argument {Object[]|undefined} o options
         * @returns {methods} 
         */          
        exclamation: function(o){defaults.state = 'exclamation'; return  methods.state(o);},
        /**
         * Cambia los estilos al estado predefinido en defaults.css.animate
         * @type function
         * @method animate
         * @memberof methods
         * @argument {Object[]|undefined} o options
         * @returns {methods} 
         */          
        animate: function(o){defaults.state = 'animate'; return methods.state(o);},
        /**
         * Cambia los estilos al estado predefinido en defaults.css.success
         * @type function
         * @method success
         * @memberof methods
         * @argument {Object[]|undefined} o options
         * @returns {methods} 
         */          
        success: function(o){defaults.state = 'success'; return methods.state(o);},
        /**
         * Cambia los estilos según los parámetros pasados
         * @type function
         * @method state
         * @memberof methods
         * @argument {Object[]|undefined} o options
         * @returns {methods} 
         */          
        state: function(o){
           let $this = $(Grot.selector); 
            if($this.length){
                $.each($this, function( index, value ) {
                    _normalize.call($(value));
                    _set.call($(value), o);
                });
            }else{
                console.log('undefined length para Grot.icon: '+typeof Grot.selector);//throw ?
            }
            return methods;
        } 
    };
    
    /**
     * Permite animar iconos de boostrap.
     * Práctico para animar un icono usando los atributos data-*<br>
     * <br>
     * Para un icono de bootstrap con la clase:<br>
     *      fa fa-save submit<br/>
     * <br>     
     *  Crear un objeto con la propiedad onclick:<br/>
     *      Grot.icon('.submit').animate({wait: 500});
     * @name Grot.icon   
     * @class                                                                       
     * @example Grot.icon(selector)[method]([arguments])
     * @param {Object|string} selector Objeto 
     * @return {methods} Métodos públicos.
     */         
    Grot.icon = function(method) {
		if ( methods[method] ) {
		  return methods[ method ].apply($(Grot.selector) , Array.prototype.slice.call( arguments, 1 ));
		}else if ( typeof method === 'object' || ! method ) {
		  return methods.getSubject.apply( $(Grot.selector), arguments );
		}else{
		  console.log( 'Method ' +  method + ' inexistente en Grot.icon' );
		}
                return this
	};   
  // check to evaluate whether "namespace" exists in the
  // global namespace - if not, assign window.namespace an
  // object literal    
}( window.Grot = window.Grot || {}));