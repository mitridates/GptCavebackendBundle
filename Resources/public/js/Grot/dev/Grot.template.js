/**
 * @var {Object} Grot "Top" namespace asignado a un object literal
 */
var Grot = Grot || {};
var GrotCache = GrotCache || new GrotPageCache();
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
     * opciones por defecto
     * @private
     * @type {Array[]}
     */  
    var defaults= {
        path: '../tpl/',
        datatype: 'text',
        overwriteFile: false,
        overwriteId: false
    };
    /**
     * Cache de archivos
     * @private
     * @type {Array[]}
     */   
    var fileCache = {},
    /**
     * Mustache mustacheInstance
     * @private
     * @type {Mustache|null}
     */   
       mustacheInstance = null,    
    /**
     * Cache de templates
     * @private
     * @type {Array[]}
     */      
    idCache= {},
    _isEmptyCache= function (){
        for(var prop in idCache) {
            if(idCache.hasOwnProperty(prop))
                return false;
        }

        return JSON.stringify(idCache) === JSON.stringify({});
    },
    _getGlobalCache= function(){
        //return Grot().cache('get','Grot.template.cache');
        return GrotCache.get('Grot.template.cache');
    },
    _setGlobalCache= function(fc, ic){
        var g = _getGlobalCache();
        if(g !== null){
            Object.assign(g.idCache, ic);
            Object.assign(g.fileCache, fc);
        }else{
            g = {
                idCache: ic,
                fileCache: fc
            };
        }
        GrotCache.set('Grot.template.cache', g);
    },
    _restoreGlobalCache= function(){
        var g = _getGlobalCache();
         if(g !== null){
            idCache = g.idCache;
            fileCache = g.fileCache;
        }       
        return;
    },
    /**
     * Metodos disponibles para Grot.tpl
     * @private
     * @type {Object[]} methods
     */           
    methods =
    {   
        set:function(o){
            var o = o || {};
            for(var i in defaults){
                if(o.hasOwnProperty(i)) defaults[i]= o[i];
            } 
            return methods;
        },
        print:function(templateId, dataTorender, target){
            if(!target){
                $(Grot.selector).append(methods.render(templateId, dataTorender));
            }else{
                $(target).append(methods.render(templateId, dataTorender));
            }
        },        
        /**
         * Carga un fichero tpl de mustache
         * @public
         * @argument {string} file file path from root ej. usuario/login.mst
         * @argument {on} file file path from root ej. usuario/login.mst
         * @returns {this}
         */         
        load: function (file, after) {
            
            if(methods.isLoaded(file)){
                if(!defaults.overwrite) return;
            }else{
                fileCache[file]= new Array();                
            }
             $.get(defaults.path+file,{}, function(template) {
                var scripts = $(template).find('script');
                scripts.each(function (i, el) {
                        methods.add(el.id, $(el).html());
                        fileCache[file].push(el.id);
                });
                   
                  if (typeof after === 'function') {
                        after();
                }
                 _setGlobalCache(fileCache, idCache);

            }
            );

/**
 * Con $.ajax({})
 */          
//            return $.ajax({
//                        url: defaults.path+file,
//                        dataType: defaults.datatype
//                    }).done(function (templates) {
//                        var scripts = $(templates).find('script');
//                        scripts.each(function (i, el) {
//                                methods.add(el.id, $(el).html());
//                                fileCache[file].push(el.id);
//                        });
//                        if (typeof after === 'function') {
//                                after();
//                        }
//                });
/**
 * Con DOMParser()
 */
//                        var doc = new DOMParser().parseFromString(templates, 'text/html');
//                        var scripts = doc.getElementsByTagName('script');
//                        for(var i = 0;i < scripts.length; i++)
//                        {
//                           methods.add(scripts[i].id, scripts[i].innerHTML);
//                        }                
                        return methods;
	},       
	/**
	 * Añadir templates
	 *
	 * @param {string} id
	 * @param {text} html para mustache
	 * @throws exception
	 */
	add: function (id, tpl) {
            if (methods.has(id) && !defaults.overwriteID) {
                    $.error('TemplateName: ' + id + ' is already mapped.');
                    return;
            }
            idCache[id] = tpl.trim();
            return;
        },
	/**
	 * @return {boolean} Existe id
	 */
	isLoaded: function(file) {
            
		return typeof fileCache[file] !== 'undefined';
	},       
	/**
	 * @return {boolean} Existe id
	 */
	has: function(id) {
		return typeof idCache[id] !== 'undefined';
	},
	/**
	 * @return {Mustache} Mustache del contexto global
	 */        
	getMustache: function() {
            if (mustacheInstance === null) {
                    mustacheInstance = window.Mustache;
                    if (mustacheInstance === void 0) {
                            console.log("Mustache no encontrado o aún no ha sido cargado en window");
                    }
            }
            return mustacheInstance;
	},
	/**
         * @param {string} id script id
         * @param {Object[]} data datos para renderizar
	 * @return {Mustache} Mustache del contexto global
	 */    
	render:function(templateId, dataTorender) {
                var template;
                /*
                 * El wrapper html. Interesa innerHTML
                 *  - script id="error-template" type="x-tmpl-mustache" : script con html
                 *  - div id="error-template" style="display:none"> : div oculto
                 */
                if(typeof templateId ==='object' && templateId.hasOwnProperty('id')) {
                    template = methods.getMustache().render(templateId.innerHTML, dataTorender);
                    //return methods.getMustache().to_html(document.getElementById(templateId).innerHTML, dataTorender||{});
                /*
                 * Hemos cargado con load() un fichero externo 
                 */
                }else if(!_isEmptyCache() && methods.has(templateId)){
                    template = methods.getMustache().render(idCache[templateId], dataTorender);
                     
                    //return methods.getMustache().to_html(idCache[templateId], dataTorender||{});
                /*
                 * Pasamos directamente una cadena de texto renderizable
                 */
                }else if(typeof templateId === 'string'){//no hay otra, le estamos pasando 
                    template = methods.getMustache().render(templateId, dataTorender);
		}else{
                    console.log('El id no está registrado: ' + templateId);
                    return null;
                }
                return template;
		
	},   
        /**
         * Clear chache
         * @public
         * @returns {this}
         */
        clear: function(){fileCache = idCache = {};},
        getCache: function(){ return idCache;},
        getFilecache: function(){ return fileCache;},
    };

   /**                                                                                                                                                                                   
    * Nueva función
    * @name Grot.template
    * @function
    * @param {string|undefined} method - Llamada a alguno de los metodos existentes
    * @return {*} methods|array... Depende si tiene sentido el encadenamiento o es una función para obtener datos
    */     
    Grot.template = function(method) {
            _restoreGlobalCache();
            if ( methods[method] ) {
              return methods[ method ].apply($(Grot.selector) , Array.prototype.slice.call( arguments, 1 ));
            }else if ( typeof method === 'object' || ! method ) {
              return methods.set.apply( this, arguments );
            }else{
              console.log( 'Method ' +  method + ' inexistente en Grot.template' );
            }
    };         
   

//   check to evaluate whether "namespace" exists in the
//   global namespace - if not, assign window.namespace an
//   object literal    
}( window.Grot = window.Grot || {}));
/**
 * Los ficheros se cargan con load:
 * Grot().template('set',{'path': '{{params.webroot~params.webasset.tpl}}'})
 * .load('uno.mst');Load es asincrono, primero hay que cargar el fichero
 * 
 * No funciona llamar acto seguido a print!!! :
 * Grot().template('print', 'hello-world', {name: 666});
 * 
 * Sí funciona en un enlace, etc..., tras la carga.
 * <a href="#" onclick="Grot('#id').template('print', 'hello-world', {name: 666});">Test templates</a>
 */
