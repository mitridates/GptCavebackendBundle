/**
 *
 * @class Clase para nested select
 * @requires EventEmitter
 * @constructor
 * @name Repopulate
 * @param {string|Object} selector
 */
function Repopulate(selector) {
    this.root  =  (typeof selector === "string" )? document.querySelector(selector) : selector;
    this.xhr_method= 'POST';
    this.listeners = {};
    this.emitter = new EventEmitter();
    if(this.root==null || this.root.tagName.toLowerCase()!=='select'){
        console.log('Tipo de selector equivocado "'+  typeof selector  +'", se esperaba "SELECT"');
    }
}


Repopulate.prototype.on = function (event, listener) {
    this.emitter.on(event, listener);
};

/**
 * @name Repopulate.bindAll#bindAll
 * @method
 * @param {Object} [element]
 * @returns this
 */
Repopulate.prototype.bindAll = function (element){
    let nodeList,
        i=0,
        elem = element||this.root,
        $this = this,
        evnt = function(elem, child){

            if(elem===$this.root && elem.form){//catch form reset
                elem.form.addEventListener("reset", function(){
                    $this.clearSelectors(child, true);
                });
            }

            elem.addEventListener("change", function(){
                $this.clearSelectors(child, true);
                if (elem.options.length===0 || (elem===$this.root && elem.options[elem.selectedIndex].value===0)) return;
                $this.populateChild(child, elem.options[elem.selectedIndex].value);
            });

            child.addEventListener("change", function(){//repopulate before load page if child has one option available
                 if(child.options.length===2 && !child.options[0].value){//has at least one value and first one is empty
                    if(!child.options[child.selectedIndex].value)//selected value is null
                    {
                        $this.clearSelectors(child);
                        let parent= elem.options[elem.selectedIndex].value;
                        if(parent) $this.populateChild(child, elem.options[elem.selectedIndex].value);
                    }
                }

            });
        };

        nodeList= document.querySelectorAll(elem.dataset.child); //static NodeList

    for(; i<nodeList.length; i++)
    {
        evnt(elem, nodeList[i]);
        if (document.querySelectorAll(nodeList[i].dataset.child).length>0) this.bindAll(nodeList[i]);
    }

    return this;
};

/**
 * @name Repopulate#clearSelectors
 * @method
 * @param {Object} elem
 * @param {boolean} deep
 * @returns this
 */
Repopulate.prototype.clearSelectors = function(elem, deep){
    let i=0, nodeList, nodeItem;

    elem.options.length=0;

    if(deep){
        nodeList = document.querySelectorAll(elem.getAttribute('data-child'));
        for(; i<nodeList.length; i++)
        {
            nodeItem = nodeList[i];
            nodeItem.options.length=0;
            if (document.querySelectorAll(nodeItem.getAttribute('data-child')).length>0) this.clearSelectors(nodeItem, true);
        }
    }
};

/**
 * @name Repopulate#populateChild
 * @method
 * @param {Object} el element type SELECT
 * @param {string} value get options by this value
 * @returns this
 */
Repopulate.prototype.populateChild = function(elem, value){
    let attr = this.getDataAttr(elem),
        formdata= null,
        $this = this,
        xhr = new XMLHttpRequest();

    if(this.xhr_method==='POST')
    {
        formdata= this.buildQueryPost(attr, value);
    }else{
        attr.url += this.buildQueryGet(attr, value);
    }

    xhr.open(this.xhr_method, attr.url, true);
    xhr.onload = function () {
        $this.setOptions(elem, $this.emitter.emit('ev.onLoadResponse', this, elem, attr)[0] || this.response, attr);
    };
    xhr.send(formdata);

    return this;
};


/**
 * Build a query string from an object of data
 * @param  {Object} data The data to turn into a query string
 * @return {String}      The query string
 */
Repopulate.prototype.buildQueryGet = function(attr, value){
    let key,
        query = [],
        parameters = JSON.parse(attr.parameters)||{};

    parameters[attr.parentid]= value;

    for (key in parameters) query.push(encodeURIComponent(key) + '=' + encodeURIComponent(parameters[key]));

    return query.length? '?'+query.join('&') : null;
};

/**
 * Build Post Data
 * @return {FormData} data to post
 * @param {Object} attr
 * @param {string} value
 */
Repopulate.prototype.buildQueryPost = function(attr, value){
    let data = new FormData(), parameters = JSON.parse(attr.parameters)||{};

    data.append(attr.parentid, value);

    for (let key in parameters) data.append(key, parameters[key]);

    return data;
};

/**
 * @name Repopulate#setOptions
 * @method
 * @param {Object} el element type SELECT
 * @param {Array} data array({id: x, value:y},...)
 * @param {string|null} returnFormat string(key,value)
 * @returns this
 */
Repopulate.prototype.setOptions = function(elem, data, attr){
    if(!data.length) return;

    let keys= (typeof attr.returnFormat === "string")? attr.returnFormat.split(',') : [Object.keys(data[0])[0], Object.keys(data[0])[1]];
    if(attr.placeholder && data[0][keys[1]]!==''){
        let option = document.createElement('option');
        option.text = attr.placeholder;
        option.value = '';
        elem.add(option);
    }

    for (let i = 0; i < data.length; i++) {
        let option = document.createElement('option');
        option.text = data[i][keys[0]];
        option.value = data[i][keys[1]];
        elem.add(option);
    }
};

/**
 * @name Repopulate#getSelector
 * @method
 * @param {Object} el element type SELECT
 * @returns this
 */
Repopulate.prototype.getDataAttr = function(el){
    return {
        url: el.getAttribute('data-url'), //url | window[json=VariableName] | function.call(this, parameter)
        parentid: el.getAttribute('data-parentid'),//ParentQuerykey if request: url?ParentQuerykey=ParentSelectedValue
        parameters: el.getAttribute('data-parameters'),//json enconded parameters key=value
        child:el.getAttribute('data-child'),//
        placeholder: el.getAttribute('data-placeholder'),//string
        returnFormat: el.getAttribute('data-returnFormat'),//return format keys names ex.: "text,value"
        origin: el.getAttribute('data-origin'),//original data "text,value"
    };
};