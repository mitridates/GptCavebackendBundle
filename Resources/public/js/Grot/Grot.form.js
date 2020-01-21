/**

 * @module Grot/form
 * @version 1.0
 * @param {Object} Grot namespace
 * @param {undefined} undefined
 * 
 */
(function(Grot, undefined) {

    /**
     * @description Distintas acciones sobre un formulario
     *      Grot .form($('.formulario')).serialize();
     *      Grot.form('.formulario').serialize();
     * @name Grot.form
     * @requires Grot.spinner
     * @requires jQuery
     * @public
     * @function
     * @param {string|undefined} method - Llamada a alguno de los metodos existentes
     * @return {*} methods|array... Depende si tiene sentido el encadenamiento o es una función para obtener datos
     */
    Grot.prototype.form = function(method) {
        if ( methods[method] ) {//llamada al method, puede haber argumentos
            return methods[ method ].apply(this , Array.prototype.slice.call( arguments, 1 ));
        }else{//no hay funcion init, asi que nos vamos
            console.log( 'Method ' +  method + ' inexistente en Grot.form' );
        }
    };
    // check to evaluate whether "namespace" exists in the
    // global namespace - if not, assign window.namespace an
    // object literal

    /**
     * Metodos disponibles para grotte.form
     * @name methods
     * @private
     * @type {Object} methods
     */
    let methods=
    {
        /**
         *  Retorna el objeto/objetos input de un selector del dom
         * El selector puede ser uno o varios selectores Grot('#selecor'|'selector1, ...').form
         * Busca dentro de nodos DOM Grot(#form|#table|#div|#td...)
         * @private
         * @returns {Array}
         */
        getArrayObject: function () {
            let types = ['input', 'select', 'checkbox', 'select'],
                arr = [];

            $(this._selector).each(function( index ) {
                if($(this).is(types.join(', '))){//es 1-n objetos : [selector, ...]
                    $(this).each(function () {
                        arr.push(this);
                    });
                }else{ //Find into dom element
                    $(':input:not(:button, :submit, :reset, :hidden)', this).each(function () {
                        arr.push(this);
                    });
                }
            });
            return arr;
        },
    /**
     * Hace un toggle disable al formulario o selector sobre el que se aplica
     * @param {bool|null} dsbl
     * @returns {Object} Grot
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
    *           Grot('#formid1, #formid2, ...').form('clear')
    *           Grot('#tableid').form('clear')
    *           Grot('#form').form('clear')
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
    * @returns {Grot.form.metods}
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

        Grot.spinner.show();
        $.post(url ,d,
            function(data) {
                Grot.spinner.hide();
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
         * @returns {Grot.form}
         */
        submit: function(url, o){

            let serialized = $(this._selector).serializeArray();
            let options = o||{};

            for (let i in options.parameters||{})
            {
                serialized.push({name: i, value: options.parameters[i]});
            }

            let callback = (typeof options.callback != "undefined")? options.callback : {};

            Grot.spinner.show();

            $.post(url ,serialized,
                function(data) {
                    Grot.spinner.hide();
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
}( window.Grot = window.Grot || {}));