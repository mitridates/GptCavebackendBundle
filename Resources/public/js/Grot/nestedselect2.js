/**
 * asigna un evento al objeto parent para cargar en this un array con select2
 *
 *     $('.municipality').select2({ //bind .municipality
 *     placeholder: '{{ 'select.government.level.admin3'|trans({},'cavemessages') }}',//opciones de select2
 *     'allowClear': true
 *     }).ajaxSelect2({
 *     url: '{{path('cave_json_admin3')}}',//ajax url
 *     preset: [{row: 'admin1code',val: 'ES.31'}],//cargar esta consulta onload
 *     parent: {el:'.province', row: 'admin2id', event:'change'},//parent recarga this onChange
 *     others: [{el:'.country', row: 'countrycode'}],//se puede añadir otros objetos para cargar sus valores
 *     defaults: {placeholder: '{{ 'select.government.level.admin3'|trans({},'cavemessages') }}'}//el mensaje por defecto
 *     });
 *
 *     También de forma anónima $.nestedselect2('.selector/id',...);
 * */

(function($) {
	var options = {
	            url:'',//url con respuesta json
               selector: false,//podemos asignarlo a otro elemento distinto de this: .class o #id
                 method: 'POST',
       allowEmptyFilter: false,
                  //array, Con preset rellenamos el select con una consulta al cargar la página
                  //row va como clave en post data
                  //ejem: {row: 'countrycode',val: 'ES'}, {row: 'admin1code',val: 'MU'},
                  //ejem buscando string [{row: 'name',val: 'alcan'},{row: 'name2',val: 'alju'}]
                 preset: [],
                 //parent to bind {el: 'id/class/jquery.object', row: 'countrycode/admin1code'}
                 parent: {el:null, row:null, event:'change'},
                 others: [],//otros parents, igual que parent [{parent1},{parent2}]
	    grandParent:{},//array de grandparents como parent
            placeholder: null,//set default placeholder
	    	   data:{//default post data
	                format:'select2',//suggest/select2...
	           emptyOption: true,
	                   max: 0,//max rs, 0 = infinito
	                    id: 'code',//return id, opcionalmente rs(fila de tabla)
	                  text: 'name',	//Texto de la columna que se mostrar
	                    rs: true,	//item.rs lleva toda la fila
            		},
	      defaults:{//select2 tiene variables mínimas
	    minimumInputLength: 0,
	           placeholder: 'Seleccione opción...',
	            allowClear: true,//allow empty option
	                 width: '100%',
			},
            },
            methods=
            {		
              init: function(o){
                    o = o || {};
                    o = $.extend(true,{},options,o);
			//buscamos el objeto al que asignar el evento
                    var $this = methods.getObject.call(this, o.selector);
                    if(!$this){
                       // console.log('No se pudo encontrar el objeto');
                        return this;
                    }
		//poblar select con búsqueda preestablecida al iniciar el script
                    if(o.preset.length){
                    	var def = $.extend(true,{}, o.defaults);
                    	var vars = $.extend(true,{}, o.data);                    	
	                    for(i=0; i<o.preset.length; i++){
	                    	vars[o.preset[i].row] = o.preset[i].val
	                    }
                    	methods.ajax.call($this, o, def, vars);
                    }

                    if(o.parent.el===null){ //no hay parent. Terminamos.
                        return $this;
                    }else{//set ob
        		    o.parent.ob = methods.getObject.call(o.parent.el, o.parent.el);
	    		    if(!o.parent.ob){ //no hay parent. Terminamos.
                	        return $this;
                    	    }
                    }

                    if(o.others.length!==0){ //set otros parents
	                    for(i=0; i<o.others.length; i++){
	                    	o.others[i].ob = methods.getObject.call(o.others[i].el, o.others[i].el);
	                    }
                    }

                    //bind parent
                    if(o.parent.hasOwnProperty('event')){
                        o.parent.ob.on( o.parent.event, function(){
                            methods.events[o.parent.event](o, $this, o.parent, o.others);
                        });
                    }else{
                    	o.parent.ob.on('change', function(){
                            methods.events.change(o, $this, o.parent, o.others);
                        });
                    }

                    return $this;
            },
            events:{
                change:function(o, $this, parent, others){

                        var def = $.extend(true,{}, o.defaults);
                        var vars = $.extend(true,{}, o.data);
                        var filter = {vars:[],isnull:function(f){
                              for(i=0; i<f.vars.length; i++){
                                  if(f.vars[i].val!==null) return false;
                              }
                              return true;
                        }};
                        vars[parent.row] = methods.getValue(parent.ob);
                        filter.vars[filter.vars.length] = {
                            'val': vars[parent.row],
                            'var': parent.row
                        }
                        if(others.length!==0){ //set post values from other parents
	                    for(i=0; i<others.length; i++){
                                if(o.others[i].hasOwnProperty('ob')){
                                   vars[o.others[i].row] = methods.getValue(o.others[i].ob);
                                   filter.vars[filter.vars.length] = {
                                       'val': vars[o.others[i].row],
                                       'var': o.others[i].row
                                   }
                                }
                            }
                        }
                    if(filter.isnull(filter) && !o.allowEmptyFilter){
                        $this.html('').select2(def);
                        return $this;
                    }else{
                        methods.ajax.call($this, o, def, vars);
                        return $this;
                    }
                }

            },

            ajax:function(o, def, vars){
                $this = this;
                $.ajax({
                  url: o.url,
                  dataType: 'json',
                  type:'POST',
                  data: vars,
                  success: function(result){
                    def.data = result.out;
                    $this.html('').select2(def).val('');
                  }
                });
            },
            getObject:function(ob){
                if(ob && $(ob).length){//es un selector
                    return $(ob);
                }
                if(this instanceof jQuery && this.length){//es objeto
                        return this;
                }
                return false;//no encontrado
            },
            getValue:function(s){
                    var val = s.val();
                    if(val===null || val==='undefined' || val.length===0){
                        return null;
                    }else{
                        return val;
                    }
                }
            };
	
	
    $.nestedselect2 = $.fn.nestedselect2 = function(method) {
		if ( methods[method] ) {//llamada al method, puede haber argumentos
		  return methods[ method ].apply( this, Array.prototype.slice.call( arguments, 1 ));
		} else if ( typeof method === 'object' || ! method ) {//el primer elemento son los argumentos (sin metodo)
		  return methods.init.apply( this, arguments );//aplicamos los arg sobre el elem o lo creamos dinamicamente
		} else {
		  $.error( 'Method ' +  method + ' inexistente en jQuery.nestedselect2' );
		}
	};


})(jQuery);
