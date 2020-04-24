'use strict';

(function(JSimas, undefined) {

    /**
     * Distintas acciones sobre un formulario
     * @name JSimas#form
     * @param {string} method - Llamada a metodo
     */
    JSimas.prototype.form = function(method) {
        if ( methods[method] ) {//method, {arguments}
            return methods[ method ].apply(this , Array.prototype.slice.call( arguments, 1 ));
        }else{
            console.log( 'Method ' +  method + ' inexistente en JSimas.form' );
        }
    };

    let spinner= (JSimas.hasOwnProperty('spinner'))? {show:() => null, hide:() => null} : JSimas.spinner,

        methods=
        {
            /**
             * Obtener del dom||formulario||objeto los elementos ['input', 'select', 'checkbox'] en un array
             * @param {Object} [f]
             * @returns {Array}
             */
            getArrayObject: function (f) {
                let arr= [],
                    filter = f || 'input:not(:button :submit :reset :hidden), select, checkbox',
                    filterNode= function(el)  {
                        if(el.matches(filter)){//es 1-n objetos : [selector, ...]
                            arr.push(el)
                        }
                    };

                this._selector.forEach(
                    function(el, i, list) {
                        if(el.hasChildNodes()) el.querySelectorAll(filter).forEach(function(e) {filterNode(e)})
                        else filterNode(el)
                    }
                );

                return arr;
            },
            /**
             * Hace un toggle disable al formulario o selector sobre el que se aplica
             * @param {bool|null} dsbl
             * @returns {Object} JSimas
             */
            disable: function(dsbl){
                methods.getArrayObject.call(this).map(function (elem) {
                    elem.disabled = typeof dsbl === "boolean" ? dsbl :  !elem.disabled;
                });
                return this;
            },
            /**
             * Borra todos los valores de un formulario o campo
             * @param {Object} form
             * @example :
             *           JSimas('#formid1, #formid2, ...').form('clear')
             *           JSimas('#tableid').form('clear')
             *           JSimas('#form').form('clear')
             * @returns {Object} form
             */
            clear: function(){
                methods.getArrayObject.call(this).map(function (elem) {
                    elem.className.indexOf('select2')!= -1 ?  $(elem).val(null).trigger('change') : elem.value = '';
                    $(elem).removeAttr('checked').removeAttr('selected');
                });
                return this;
            },
            /**
             * Submit para un formulario con jquery
             * @param {string} url
             * @param {Array} p params
             * @param {object} c callback array {{before, success, error, after}, {...}, ...}
             * @returns self
             */
            enviar: function(url, p, c){

                let d = (this._selector !=='') ? $(this._selector).serializeArray() : {} ;
                c = c || {};
                //add params
                for (let i in p||{})
                {
                    d.push({name: i, value: p[i]});
                }
                //before send callback
                if(typeof c.before=== "function") c.before.call(this._selector);

                spinner.show();

                $.post(url ,d,
                    function(data) {
                        spinner.hide();
                        if(typeof c === 'function'){
                            c.call(this._selector, data);
                            return;
                        }
                        //Mensajes del controlador: postsubmit callback
                        if(typeof data.error !== 'undefined'){
                            if(typeof c.error===  'function'){
                                c.error.call(this._selector, data);
                            }else{
                                console.log(data.error);
                            }
                        }else{
                            if(typeof c.success=== 'function') c.success.call(this._selector, data);
                            //ejecutar after
                            if(typeof c.after=== 'function') c.after.call(this._selector, data);
                        }
                    }
                );
                return this;
            },
            /**
             * Submit para un formulario con jquery
             * @param {string} url Post url
             * @param {Object} o Options {parameters: {name:value}, callback:function||{error:function, success:function}}
             * @returns {JSimas.form}
             */
            submit: function(url, o){

                let serialized = $(this._selector).serializeArray();
                let options = o||{};

                for (let i in options.parameters||{})
                {
                    serialized.push({name: i, value: options.parameters[i]});
                }

                let callback = (typeof options.callback != "undefined")? options.callback : {};

                spinner.show();

                $.post(url ,serialized,
                    function(data) {
                        spinner.hide();
                        if(typeof callback === 'function'){
                            callback.call(this._selector, data);
                            return;
                        }
                        //Mensajes del controlador: postsubmit callback
                        if(typeof data.error !== 'undefined'){
                            if(typeof callback.error ===  'function'){
                                callback.error.call(this._selector, data);
                            }else{
                                console.log(data.error);
                            }
                        }else{
                            if(typeof callback.success=== 'function') callback.success.call(this._selector, data);
                        }
                    }
                );
                return this;
            }
        };
}( window.JSimas = window.JSimas || {}));