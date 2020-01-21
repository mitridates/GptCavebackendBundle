/**
 * template.
 * Retorna una función para cargar templates con Mustache
 * Podría crearlo como objeto y soportar más librerías
 * Ej. Grot.append('template',{mustache: ...}
 * y luego llamarlo Grot.get('template').mustache();
 * Dejo la idea aquí.
 */
Grot.module.create('template', {
    name: 'template',
    description: 'Modulo para obtener templates para mustache',
    version: '1.0'
    
});
                
Grot.module.append('template', 
(function() {  
        
    /**
     * opciones por defecto
     * @private
     * @type {Array[]}
     */  
    var defaults= {
        path: '../tpl/',
        datatype: 'text',
        overwriteFile: false,
        overwriteId: false,
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
    /**
     * Metodos disponibles para Grot.tpl
     * @private
     * @type {Object[]} methods
     */           
    methods =
    {   
        set:function(o){
            for(var i in o){
                defaults[i]= o[i];
            } 
            return this;
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
            return $.get(defaults.path+file,{}, function(template) {
                var scripts = $(template).find('script');
                scripts.each(function (i, el) {
                        methods.add(el.id, $(el).html());
                        fileCache[file].push(el.id);
                });
                  if (typeof after === 'function') {
                        after();
                }
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
	render:function(id, data) {
		if (!methods.has(id)) {
                    console.log('El id no está registrado: ' + id);
		}
		return methods.getMustache().to_html(idCache[id], data||{});
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

  return methods;//Métodos expuestos
}({})));

//onload cargamos el fichero con templates
////get template loader
// var tplLoader = Grot.get('template');
////set path
//tplLoader.set({'path': '{{params.webroot~params.webasset.tpl}}'});
////load file
//tplLoader.load('uno.mst');
//
//Fichero con plantillas uno.mst: 
//<templates>
//<script id="hello-world" type="text/html">
//	<p>Hello, {{name}}, this is a basic template rendering example!</p>
//</script>
//</templates>
//
//
//En el documento llamamos al id del template
//<a href="#" onclick="
//      var names = ['fer','nan', 'do'];
//     for(var i in names){
//         $('#must').append(Grot.get('template').render('hello-world', {name: names[i]}));
//     }
//   " >Test templates</a>