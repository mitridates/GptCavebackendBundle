/*+
 * Grot(constructor arguments).object([private context method], [arguments...])
 */
/**
 * @param {Object} Grot namespace
 * @param {undefined} undefined
 */
(function(Grot, undefined) {
    var _switch= [],
    _setSwitch = function (o) {
        if(Array.isArray(o)) _switch = o.slice(0,2);
        else if (o instanceof Object) _switch = [o.on||'', o.off||'' ];
        else console.log( 'Type ' +  typeof o + ' no valido' );
        return _switch;
    },

    methods= {
        init: function (o) {
            if(o) _setSwitch(o)
            return methods[$( this._selector ).hasClass(_switch[0])? 'off': 'on'].apply(this);
        },
        off: function (o) {
            if(o) _setSwitch(o)
            $( this._selector).removeClass(_switch[0]).addClass(_switch[1]);
            return false;
        },
        on: function (o) {
            if(o) _setSwitch(o)
            $( this._selector).removeClass(_switch[1]).addClass(_switch[0]);
            return true;
        },
    };

   /**                                                                                                                                                                                   
    * Alterna entre clases y devuelve un valor bool
    * @name Grot.toggleClass
    * @function
    * @param {string|undefined} method - Llamada a alguno de los metodos existentes
    * @return {bool} estado
    */         
    Grot.prototype.toggleClass = function(method) {
        if ( methods[method] ) {
		  return methods[ method ].apply(this , Array.prototype.slice.call( arguments, 1 ));
		}else if ( typeof method === 'object' || Array.isArray(method) || ! method ) {
		  return methods.init.apply( this, arguments );
		}else{
		  console.log( 'Method ' +  method + ' inexistente en Grot.toggleClass' );
		}
	}; 

}( window.Grot = window.Grot || {}));