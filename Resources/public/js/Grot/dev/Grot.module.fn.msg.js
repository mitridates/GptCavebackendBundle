/**
 * @var {Object} Grot "Top" namespace asignado a un object literal
 */
var Grot = Grot || {};
/**
 * Añadimos una nueva definición de submodulo
 */
Grot.module.create('msg', {
    name: 'msg',
    description: 'Módulo que contiene formulas para decir algo',
    version: '1.0'
    
});
/**
 * msg base
 */
Grot.module.append('msg', {
    /**
    * Objeto base para mensajes
    * @returns {Object} función para mensajes instanciables
    * @returns {Object} Grot.modules[base]
    */        
    base: function() {
    /**
     * opciones por defecto
     * @private
     * @type {Array[]}
     */  
    var defaults= {
        output: 'log',
        level: 'success'
    },  
    /**
     * mensaje standar
     * @private
     * @type {Array[]}
     */       
    message = {
        name: null,//error type
        level: null,//error level
        text: null, //msg
        code: null,//code
        /**
         * Imprime el mensaje como string
         * @return {string}
         */            
        write: function(){ 
            return (this.name? this.name+': ' : ''  )+ this.text;
        },
        getLevel:function(){
          var level = ['success','debug', 'info', 'notice', 'warning', 'error', 'critical', 'danger'];  
          if(this.level === null) this.level = defaults.level;
          if(level.indexOf(this.level)===-1) throw 'unknow level';
          return this.level; 
        }
    },
    
    /**
     * cache de mensajes
     * @private
     * @type {Array[]}
     */   
    cache = [],
    /**
     * Metodos para Grot.msg
     * @private
     * @type {Object[]} methods
     */           
    methods = 
    {   
        set: function(o){
            for(var i in o){
                defaults[i]= o[i];
            } 
            return methods;//encadenar metodos
        },        
        /**
         * add msg
         * @public
         * @argument {message} m
         * @returns {this}
         */         
        add: function(m){
            var temp = Object.create(message);//instanciar
            if(typeof m === 'string'){
                temp['text']= m;
            }else{
                for(var i in temp){
                    if( m[i]!== undefined ) temp[i] = m[i];
                }        
            }
            cache.push(temp);
            return methods;//encadenar metodos
        },    
        /**
         * Clear chache
         * @public
         * @returns {Grot.msg}
         */
        clear: function(){
            cache = []; 
            return methods;//encadenar metodos
        },
        /**
         * get chache de mensajes
         * @public
         * @returns {Object[]}
         */
        getCache: function(){return cache;},
        /**
         * Manda el mensaje actual o último en cache a la salida
         * @public
         * @returns {Grot.msg}
         */
        show: function(m){
            var m = m||null;
            if(m !== null) methods.add(m);
            var last = cache[cache.length-1];
            if(!methods.output.hasOwnProperty(defaults.output)){
                methods.output.log(last);
            }else{
                methods.output[defaults.output](last);
            }
            return methods;//encadenar metodos
        },       
        /**
         * muestra los mensajes en cache y la vacía
         * @name flush
         * @public
         */          
        flush : function(){
            var outputmethod = (methods.output.hasOwnProperty(defaults.output))? defaults.output : 'log';
            cache.reverse();
            do{
                var m = cache.pop();
                methods.output[outputmethod](m);
            }while(cache.length!==0)
            return methods;//encadenar metodos
        },
        addOutput: function(name,callback){
          methods.output[name]= callback;  
        },
        /**
         * Muesta el mensaje
         * @public
         * @returns {this}
         */    
        output: {}       
    };
    return methods;
}});

/**
 * Extiende Grot.modules[base] con nuevos outputs
 */
Grot.module.append('msg', 
{
    /**
     * Retorna una instancia de Grot.modules[msg][base] extendida con alert
     * @param {Object} m message
     * @returns {undefined}
     */   
    alert: function(m){
        var fn = function(m){
            var out = (m.level)? m.level+ ': ': '' ;
            out += ((out.length)? ' ':'') + ((m.name)? m.name : '') ;
            out += ((out.length)? '\n':'') + m.text;
            alert(out);
        };           
        var extended = new Grot.module.get('msg').base();
        extended.set({output: 'alert'}); 
        extended.addOutput('alert', fn);
        if(typeof m !=='undefined') extended.add(m);
        return extended;
    },
    /**
     * Retorna una instancia de Grot.modules[msg][base] extendida para log
     * @param {Object} m message
     * @returns {undefined}
     */       
    log: function(m){ 
        var fn = function(m){
            console.log(m.write());
        };        
        var extended = new Grot.module.get('msg').base();
        extended.set({output: 'log'}); 
        extended.addOutput('log', fn);
        if(typeof m !=='undefined') extended.add(m);
        return extended;
    },
    /**
     * Retorna una instancia de Grot.modules[msg][base] extendida con notify
    * @param {Object} m message
    * @returns {Object} función para mensajes instanciables
    */
    notify: function(m) {
        var fn = function(m){
            //arreglo para los mensajes de bootstrap
                switch(m.getLevel()){
                 case 'debug':
                 case 'info' :
                     m.level = 'info';
                     break;
                 case 'notice':
                 case 'warning' :
                     m.level = 'warning';
                     break;
                 case 'error':
                 case 'critical' :
                     m.level = 'danger';  
                     break;
             }            
            $.notify({title: parent.environment!=='dev' ? m.name : m.name||'' + ' ('+ m.code +') '  , message: m.text},{type: m.getLevel()});
        };
        var extended = new Grot.module.get('msg').base();
        extended.set({output: 'notify'}); 
        extended.addOutput('notify', fn);
        if(typeof m !=='undefined') extended.add(m);
        return extended;
    },//end notify
   /**
    * notificación parameters:
    * {status(opcional), source.pointer(opcional), index(opcional), title(obligatorio), source.parameter: {[ name : value ](opcional)}(opcional)}
    * @param {Object} m message
    * @returns {Object} función para mensajes instanciables
    */
    notification: function(m) {
        var fn = function(m){
            
            var container = document.getElementById('form-notification'),
            template = document.getElementById('mst-form-error').innerHTML,
            rendered = Mustache.to_html(template, m);
            container.innerHTML= rendered;
            container.style.display='block';
        };
        var extended = new Grot.module.get('msg').base();
        extended.methods.flush = function(){
            fn({error: extended.cache});
            extended.cache=[];
        };
        extended.set({output: 'notification'}); 
        extended.addOutput('notification', fn);
        if(typeof m !=='undefined') extended.add(m);
        return extended;
    }  
});
//Grot.module.get('msg').alert({name: 'nombre alert', level: 'warning','text': 'primero'}).add({'text': 'Hola mundo'}).add({'text': 'despues'}).flush();
//
//Grot.module.get('msg').notify({level: 'info',text: 'primero'}).add({level: 'notice','text': 'Hola mundo'}).add({level: 'error','text': 'despues'}).flush();
//
//var logger = Grot.module.get('msg').log();
//logger.add('Se ha producido un error').        
//        add('la verdad, no sé cual').
//        add('pues nada, hasta luego.');
//logger.flush();