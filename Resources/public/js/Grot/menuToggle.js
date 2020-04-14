/**
 * Create complex navigation menu
 * @class MenuToggle
 * @param {String} menu Parent container ID for menu items
 * @param {Object} options Options to initialize the component with
 */
function MenuToggle (menu, options) {

    this.menu  = document.getElementById(menu);
    //https://stackoverflow.com/questions/10490713/how-to-document-the-properties-of-the-object-in-the-jsdoc-3-tag-this
    //https://developer.mozilla.org/es/docs/Web/JavaScript/Referencia/Objetos_globales/Object/defineProperty
    // /**
    //  Menu targets from hash items
    //  @name MenuToggle#dataTargets
    //  @type Array
    //  */
    // Object.defineProperty(this, 'dataTargets', {
    //     value: [],
    //     set(v) {
    //     }
    // });
    //this.dataTargets= ;
    this.targetsContainer = document.getElementById(this.menu.dataset.container);
    /**
     This event emitter
     @name MenuToggle#emitter
     @type Object
     @default null
     */
   // this.emitter = (!EventEmitter && typeof EventEmitter != 'function')? null :  new EventEmitter();
    Object.defineProperty(this, 'emitter', {
        value: (!EventEmitter && typeof EventEmitter != 'function')? null :  new EventEmitter(),
        writable: false
    });
    if (!this.targetsContainer ) throw new DOMException('Undefined menu destination container')
}

MenuToggle.prototype = {
    menuItemsSelector: 'a',
    visitedSelector: 'color-visited',
    activeSelector: 'active',
    getMenuNodeList: function()
    {
        return this.menu.querySelectorAll(this.menuItemsSelector);
    }
};

MenuToggle.prototype.on = function (event, listener) {
    if(this.emitter) this.emitter.on(event, listener);
    return this;
};

/**
 * Init menu
 */
MenuToggle.prototype.init= function()
{
    if(this.getMenuNodeList().length===0) {
        console.log('Menu elements not found');
        return;
    }
    if(this.emitter) this.emitter.emit('init');
    let itemListener,
        i=0,
        $this= this,
        itemsNodeList = this.getMenuNodeList();

    for(; i<itemsNodeList.length; i++){
        itemListener = {
            el: itemsNodeList[i],
            handleEvent: function (event) {
                if (event.type === 'click') {
                    if($this.emitter) {
                        /**
                         * Event for this menu element
                         * @example menuToggle.on('click.dataTargetId', function(menuItem, dataTarget){})
                         */
                        $this.emitter.emit('click.'+this.el.hash.substr(1), this.el, document.querySelector(this.el.hash));
                        /**
                         * Event for all menu elements
                         * @example menuToggle.on('click', function(menuItem, dataTarget){})
                         */
                        $this.emitter.emit('click', this.el, document.querySelector(this.el.hash));
                    }
                    history.pushState(null, null,  this.el.href)
                    event.preventDefault();
                    $this.show(this.el)
                }
            }
        };

        itemsNodeList[i].addEventListener('click', itemListener, false);
    }

    this.updateHistory()
    this.loadHashOrDefault();


    //@todo

    return this;
};

// /**
//  * Get menu elements NodeList
//  * @return {Object|null} NodeList
//  */
// MenuToggle.prototype.getMenuNodeList= function()
// {
//     return this.menu.querySelectorAll(this.menuItemsSelector);
// }

/**
 * Load hash || active || first item
 * return self
 */
MenuToggle.prototype.loadHashOrDefault= function()
{
    let menuItem, i=0,
        source= ['[href="'+window.location.hash+'"]', '.'+this.activeSelector, ''];

    for(;i<source.length;i++){
        menuItem = this.menu.querySelector(this.menuItemsSelector+source[i]);
        if(menuItem) break;
    }
    menuItem.click();

    return this;
};


/**
 * @todo
 * Add hash to history to use back and forward buttons/keyboard sort cut
 * return {NULL}
 * @param {Object} menuItem Menu link
 */
MenuToggle.prototype.updateHistory= function()
{
    let $this= this,
        popstateEvent ={
            handleEvent: function (event) {
              let currentHistorySelector= $this.menu.querySelector($this.menuItemsSelector+'[href="'+window.location.hash+'"]');
                  //lastActiveSelector = $this.menu.querySelector('.'+$this.activeSelector);
                $this.show(currentHistorySelector);
            }
        };
    // navigate to a tab when the history changes
    window.addEventListener("popstate", popstateEvent);
};


/**
 * Load div
 * @param {Object} menuItem Menu link
 */
MenuToggle.prototype.show= function(menuItem)
{
    let el, i=0,
        previous= this.menu.querySelector('.'+this.activeSelector),
        dataTarget  = document.querySelector(menuItem.hash);

    if(typeof dataTarget === "undefined")
    {
        console.log('Target hash ID desconocido: '+ menuItem.hash);
        return false;
    }


    if(previous) previous.classList.remove(this.activeSelector);

    menuItem.classList.add(this.activeSelector, this.visitedSelector);

    for (; i < this.targetsContainer.children.length; i++)
    {
        el = this.targetsContainer.children[i];
        el.style.display = 'none';

        if(this.emitter)
        {
            /**
             * Hide event for for all menu elements
             * @example menuToggle.on('click.dataTargetId', function(menuItem, dataTarget){})
             */
            this.emitter.emit('hide', el);
            /**
             * Hide event for this menu element
             * @example menuToggle.on('hide.dataTargetId', function(dataTarget){})
             */
            this.emitter.emit('hide.' + el.id, el);
        }
    }

    dataTarget.style.display= 'inline';

    if(this.emitter)
    {
        /**
         * Show event for this menu element
         * @example menuToggle.on('hide.dataTargetId', function(dataTarget){})
         */
        this.emitter.emit('show.'+dataTarget.id, dataTarget);
    }
};