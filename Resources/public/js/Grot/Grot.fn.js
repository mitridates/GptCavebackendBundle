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
  }
};