/**
 * @var {Object} Grot "Top" namespace asignado a un object literal
 */
var Grot = Grot || {};
/**
 * Añadimos una nueva definición de submodulo
 */
Grot.module.append('fn', {
    /**
     * Callback para un formulario jQuery $.post
     * @example Grot.fn('animatebtn', this);
     * @param {Object|string} selector con icono animable.
     * @return {Object}
     */
    icon:{
        animateCb:function(selector){
            return {
                    before: function(){
                        Grot(selector).icon.animate();
                    },
                    success: function(data){ 
                        Grot(selector).icon.success({wait: 1});
                    },
                    error: function(data){
                        Grot(selector).icon.exclamation({wait: 1});
                    }
                };
            }

    }    
});