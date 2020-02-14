/**
 * @class
 * @requires EventEmitter
 * @constructor
 * @name Togglemenu
 * @param {Object} menu Container for menu items
 */
function Togglemenu (menu) {
    this.menu  = menu;
    this.listeners = {};
    this.emitter = new EventEmitter();
    this.targetsContainer = document.getElementById(menu.dataset.container);
    this.menuItemsSelector= 'a';
    if(null===this.targetsContainer) console.log('Contenedor de destino no definido');
}

/**
 * Add event
 */
Togglemenu.prototype.on= function (event, listener) {
    this.emitter.on(event, listener);
};
/**
 * Add event
 */
Togglemenu.prototype.of= function (event, listener) {
    this.emitter.of(event, listener);
};

/**
 * Bind menu
 */
Togglemenu.prototype.init= function()
{
    let $this= this;
    this.on('click', function (menuItem, dataTarget) {$this.load(menuItem)});
    return this;
}

/**
 * Get menu nodeList
 * return self
 */
Togglemenu.prototype.getMenuNodeList= function()
{
    return this.menu.querySelectorAll(this.menuItemsSelector);
}

/**
 * Load hash || active || first item
 * return self
 */
Togglemenu.prototype.loadHashOrDefault= function()
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
 * @param {Object} [menuItem] Menú link
 */
Togglemenu.prototype.showContainerDiv= function(dataTarget, menuItem)
{
    for (let i = 0; i < this.targetsContainer.children.length; i++) {
        let e = this.targetsContainer.children[i];
        e.style.display = 'none';
    }
    $this.emitter.emit('ev.show', dataTarget, menuItem);
    dataTarget.style.display= 'inline';
}

/**
 * Load/reload div
 * @param {Object} menuItem Menú link
 * @param {boolean} [reload] reload content
 */
Togglemenu.prototype.load= function(menuItem, reload)
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


    if(!is_loaded) menuItem.dataset.loaded='true';
    this.showContainerDiv(dataTarget, menuItem);
    this.menu.querySelector('.active').classList.remove('active')
    menuItem.classList.add('active');
}