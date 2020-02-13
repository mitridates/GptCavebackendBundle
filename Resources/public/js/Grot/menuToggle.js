/**
 * @class
 * @constructor
 * @name menuToggle
 * @param {Object} menu Container for menu items
 */
function menuToggle (menu) {
    this.menu  = menu;
    this.appendHash= true;
    this.targetsContainer = document.getElementById(menu.dataset.container);
    this.menuItemsSelector= 'a';
    if(null===this.targetsContainer) console.log('Undefined menu destination container');
}

/**
 * Add event
 */
menuToggle.prototype.on= function(type, callback, hash)
{
    let menuItem, itemsNodeList, i=0, $this = this,
    addItemHandler= function(el, eventType, func){
        return {
            el: el,
            handleEvent: function (event) {
                if (event.type === type) {
                    if($this.appendHash) document.location.hash = this.el.hash;
                    event.preventDefault();
                    func(this.el, document.querySelector(this.el.hash))
                }
            }
        };
    };

    if(hash){
        menuItem = this.menu.querySelector(this.menuItemsSelector+'[href="'+hash+'"]');
        if(menuItem){
           menuItem.addEventListener(type, addItemHandler(menuItem , type, callback), false);
        }
    }else{
        itemsNodeList = this.getMenuNodeList();
        for(; i<itemsNodeList.length; i++){
            itemsNodeList[i].addEventListener(type,  addItemHandler(itemsNodeList[i], type, callback), false);
        }
    }
}

/**
 * Bind menu
 */
menuToggle.prototype.init= function()
{
    let $this= this;
    this.on('click', function (menuItem, dataTarget) {$this.load(menuItem)});
    // let item,
    //     i=0,
    //     $this= this,
    //     itemsNodeList = this.getMenuNodeList();

    // for(; i<itemsNodeList.length; i++){
    //     item = {
    //         el: itemsNodeList[i],
    //         handleEvent: function (event) {
    //             if (event.type === 'click') {
    //                 if($this.appendHash) document.location.hash = this.el.hash;
    //                 event.preventDefault();
    //                 $this.load(this.el)
    //             }
    //         }
    //     };
    //     itemsNodeList[i].addEventListener('click', item, false);
    // }
    return this;
}

/**
 * Get menu nodeList
 * return self
 */
menuToggle.prototype.getMenuNodeList= function()
{
    return this.menu.querySelectorAll(this.menuItemsSelector);
}

/**
 * Load hash || active || first item
 * return self
 */
menuToggle.prototype.loadHashOrDefault= function()
{
    let menuItem;

    menuItem = this.menu.querySelector(this.menuItemsSelector+'[href="'+window.location.hash+'"]') ||
        this.menu.querySelector(this.menuItemsSelector+'.active') ||
        this.menu.querySelector(this.menuItemsSelector);
    menuItem.click();
    return this;
}


/**
 * Show data
 * @param {Object} dataTarget data div container
 */
menuToggle.prototype.showContainerDiv= function(dataTarget)
{
    for (let i = 0; i < this.targetsContainer.children.length; i++) {
        let e = this.targetsContainer.children[i];
        e.style.display = 'none';
    }
    dataTarget.style.display= 'inline';
}

/**
 * Load/reload div
 * @param {Object} menuItem Menú link
 * @param {boolean} [reload] reload content
 */
menuToggle.prototype.load= function(menuItem, reload)
{
    let dataTarget  = document.querySelector(menuItem.hash),
        is_loaded= menuItem.dataset.loaded || false;

    if(typeof dataTarget === "undefined")
    {
        console.log('Target hash ID desconocido: '+ menuItem.hash);
        return false;
    }

    // Update history
    //window.history.pushState(null, null, menuItem.href);

    this.menu.querySelector('.active').classList.remove('active')
    menuItem.classList.add('active');
    if(!is_loaded) menuItem.dataset.loaded='true';
    this.showContainerDiv(dataTarget);
}