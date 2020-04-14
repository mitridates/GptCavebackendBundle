/*
 V.1.0
 Modal bootstrap div as form editor
 */

/**
 * Shorthand form bootstrap modal
 * @param {Object} ob Boostrap modal div
 * @requires EventEmitter
 * @throws DOMException
 */
function Modalform(ob) {
    if(typeof ob != 'object' || !ob.classList.contains('modal')){
        throw new DOMException('invalid DOM modal object, must be Bootstrap modal div')
    }
    if (!EventEmitter && typeof EventEmitter != 'function') {
        throw new DOMException('EventEmitter function does not exists!')
    }
    this.listeners = {};
    this.emitter = new EventEmitter();
    this.modal = ob;
}

Modalform.prototype.on = function (event, listener) {
    this.emitter.on(event, listener);
};
/**
 * @param string title
 * @returns {Modalmod}
 */
Modalform.prototype.setTitle= function (title) {
    this.modal.querySelector('.modal-title').innerHTML = title;
    return this;
};

/**
 * @param string data
 * @returns {Modalmod}
 */
Modalform.prototype.setBody= function (data) {
    this.modal.querySelector('.modal-body').innerHTML = data;
    return this;
};

Modalform.prototype.load= function (fetchUrl) {
    let $this = this,
    xhr = new XMLHttpRequest();
    xhr.open('POST', fetchUrl, true);
    xhr.onload = function () {
        $this.setBody(this.responseText);
        $this.emitter.emit('grot.modalmod.onLoadForm', this.responseText, $this);
        $($this.modal).modal('show').modal('handleUpdate');
    };
    xhr.send();
    return this;
};

Modalform.prototype.getBttn= function (bttn) {
    switch (bttn) {
        case 'close': return this.modal.querySelector('.modal-close');
        case 'submit': return this.modal.querySelector('.modal-submit');
    }
};
