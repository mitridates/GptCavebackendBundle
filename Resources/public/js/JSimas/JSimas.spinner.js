'use strict';
/*
 */
(function() {
    let spin = function(disp){
        let el = document.getElementById('JSimas-spinner');
        if(el === null){
            el = document.createElement('i');
            el.setAttribute('id', 'JSimas-spinner');
            el.setAttribute('class', 'fa fa-cog fa-spin fa-2x fa-fw centerbox-xy');
            document.body.appendChild(el);
        }else{
            el=  document.getElementById('JSimas-spinner');
        }
        el.style.display= disp;
    };
    /*
     * Un spinner básico para los callback para la función
     */
    JSimas.spinner = {
        show: function() { spin('inline');},
        hide: function() {spin('none');}
    };
})(window.JSimas = window.JSimas || {});