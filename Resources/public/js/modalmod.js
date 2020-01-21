/*
 V.1.0
 Modal bootstrap div as form editor
 */

/**
 * Shorthand form bootstrap modal
 * @param {Object} ob Boostrap modal div
 * @throws DOMException
 */
function Modalmod(ob) {
    if(typeof ob != 'object' || !ob.classList.contains('modal')){
        throw new DOMException('invalid DOM modal object, must be Bootstrap modal div')
    }
    this.modal = ob;
    this.doc = this.modal.querySelector('.modal-dialog')
    this.content = this.doc.querySelector('.modal-content')
    this.body = this.content.querySelector('.modal-body')
    this.header = this.content.querySelector('.modal-header')
    this.footer = this.content.querySelector('.modal-footer')
    this.footerClose = this.footer.querySelector('.modal-close')
    this.footerSubmit = this.footer.querySelector('.modal-submit')
    this.title = this.header.querySelector('.modal-title')
    this.headerClose = this.header.querySelector('.close')
}
/**
 * @returns {Modalmod}
 * @param fetchUrl
 * @param complete
 */
Modalmod.prototype.fetchForm= function (fetchUrl, complete) {
    let $this = this;
    $.post(fetchUrl,{},
        function(data) {
            $this.setBody(data);
            $($this.modal).modal('show').modal('handleUpdate');
            if(typeof complete === "function") complete.call($this, data);
        });
    return this;
};
/**
 * @param {Object} form Fetched form
 * @param {string} submitUrl
 * @param {function} complete callback if success
 * @returns self
 */
Modalmod.prototype.bindSubmit= function (form, submitUrl, complete) {
    let $this =    this;
    let submitFn =  function () {
        Grot(form).form('submit', submitUrl, {'callback': function (data) {
                if(data.length!==0){//error. Retrieve rendered form with errors
                    $this.setBody(data);
                }else{
                    $($this.modal).modal('hide').on('hidden.bs.modal', function (e) {
                        $this.footerSubmit.removeEventListener('click', submitFn, false);
                        complete.call($this, data);
                    })
                }
            }});
        return false;
    };
    form.onsubmit = function () {
        $this.footerSubmit.click(); return false;
    };
    $this.footerSubmit.addEventListener('click', submitFn);
};
/**
 * @param string title
 * @returns {Modalmod}
 */
Modalmod.prototype.setTitle= function (title) {
    this.title.innerHTML = title;
    return this;
};
/**
 * @param string data
 * @returns {Modalmod}
 */
Modalmod.prototype.setBody= function (data) {
    this.body.innerHTML = data;
    return this;
};