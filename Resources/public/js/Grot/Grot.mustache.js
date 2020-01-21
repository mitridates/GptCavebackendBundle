/*
 * Muestra un mensaje en pantalla
 * Usa Mustache
 */
Grot.mustache = function(container, template){
    "use strict";
    this.container = container||false,
    this.template = template||false;
};

Grot.mustache.prototype.hide = function(){
    document.getElementById(this.container).innerHTML = '';
    document.getElementById(this.container).style.display='none';
};

Grot.mustache.prototype.show = function(data){
    var Mustache = window.Mustache ||false,
    $this = this;
    if(!Mustache){
        alert('Mustache no definido');
        return;
    }
    var container = document.getElementById(this.container),
        template = document.getElementById(this.template).innerHTML,
        rendered = Mustache.to_html(template, data);
        container.innerHTML= rendered;
        container.style.display='block';

    document.onkeydown = function(evt) {
        evt = evt || window.event;
        if (evt.keyCode == 27) {
            $this.hide();
        }
    };
};

