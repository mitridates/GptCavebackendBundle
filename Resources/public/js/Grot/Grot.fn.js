var Grot = Grot || {};
/**
 * Algunas funciones sin contexto o que no sé donde meter que se cargan
 * al final de la página el footerjs.html.twig
 */
Grot.fn = {
  /*
   * Inicializar selectores
   */
  init: function(){
      $.fn.select2.defaults.set( "theme", "bootstrap" );
      return Grot;
  },
    ready : function(callback){
        // in case the document is already rendered
        if (document.readyState!='loading') callback();
        // modern browsers
        else if (document.addEventListener) document.addEventListener('DOMContentLoaded', callback);
        // IE <= 8
        else document.attachEvent('onreadystatechange', function(){
                if (document.readyState=='complete') callback();
            });
    }
};