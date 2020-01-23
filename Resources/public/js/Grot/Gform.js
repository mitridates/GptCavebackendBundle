(function( $ ){

    let methods = {
        /**
         *  Filter or get input object/s from dom element
         * @returns {Array}
         */
        toArray : function( ) {
            let types = ['input', 'select', 'checkbox', 'select'],
                arr = [];

            $(this).each(function( index ) {
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
         * Toggle/set disable true/false
         * @param {bool|null} dbl
         * @returns self
         */
        disable: function(dbl){
            methods.getArrayObject.call(this).map(function (elem) {
                elem.disabled = typeof dbl === "boolean" ? dbl :  !elem.disabled;
            });
            return this;
        },
        /**
         * Clear input, checkbox, select...
         * @returns self
         */
        clear: function(){
            methods.getArrayObject.call(this).map(function (elem) {
                elem.className.indexOf('select2')!= -1 ?  $(elem).val(null).trigger('change') : elem.value = '';
                $(elem).removeAttr('checked').removeAttr('selected');
            });
            return this;
        },
        submit(url, callback, jsonParams){
            let $this= this, d = $(this).serializeArray();

            for (let i in jsonParams||{}) d.push({name: i, value: p[i]});

            Gspinner.show();
            $.ajax({
                url: url,
                type: "POST",
                cache: false,
                data: d,
                processData: false,  // tell jQuery not to process the data
                contentType: false,   // tell jQuery not to set contentType+
                success: function(data){
                    Gspinner.hide();
                    if(typeof callback===  'function'){
                        callback.call($this, data);
                    }
                }
            });
            return this;
        }
    };

    $.fn.Gform = function(method) {
        if ( methods[method] ) {
            return methods[ method ].apply(this , Array.prototype.slice.call( arguments, 1 ));
        }else if ( typeof method === 'object' || ! method ) {
            return methods.submit.apply( this, arguments );
        }else{
            console.log( 'Method ' +  method + ' inexistente en Gform' );
        }
    };
})( jQuery );