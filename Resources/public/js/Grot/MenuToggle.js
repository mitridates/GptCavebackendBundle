/**
 * Mix of tabs style menu and megamenu.
 * @example
 *      <div id="containerMenuId">
 *     ...create tables, ul menus... whatever
 *     <a class="someMenuItemQuerySelectorName ..." href="#targetDiv">...</a>
 *     ...
 *     </div>
 *     ...
 *     <div id="targetDiv"></div>
 *
 *     <script>
 *     let someMenuToggle= new MenuToggle('containerMenuId')
 *     someMenuToggle.setMenuItemQuerySelector('a.someMenuItemQuerySelectorName').
 *     on('click', function (menuItem, dataTarget){
 *          //click event for all items
 *     }).
 *     on('click.targetDiv', function (menuItem, dataTarget){
 *          //click event for this item in menu
 *     }).
 *     run()
 *     </script>
 *
 * @class MenuToggle
 * @param {String} menuId Top container ID for menu items
 */
function MenuToggle (menuId) {
    this.menuContainer = document.getElementById(menuId)
    this.menuItemsSelector='a'
    this.visitedSelector='color-visited'
    this.activeSelector='active'
    this.emitter = new EventEmitter()
}

MenuToggle.prototype.setMenuItemQuerySelector= function(val){ this.menuItemsSelector= val; return this; }
MenuToggle.prototype.setVisitedSelector= function(val){ this.visitedSelector= val; return this; }
MenuToggle.prototype.setActiveSelector= function(val){ this.activeSelector= val; return this; }

/**
 * Get menu items
 * @name MenuToggle#getMenuItemsNodeList
 */
MenuToggle.prototype.getMenuItemsNodeList= function(){
    return this.menuContainer.querySelectorAll(this.menuItemsSelector)
}

/**
 * Get menu targets
 * @name MenuToggle#getDataTargets
 */
MenuToggle.prototype.getDataTargets = function(){
        let d = [], i = 0,
            items = this.getMenuItemsNodeList();
        for (; i < items.length; i++) {
            d.push(document.querySelector(items[i].hash))
        }
        return d;
}

/**
 * Add events on[click, click.itemHashName, show, show.itemHashName]
 * @name MenuToggle#on
 */
MenuToggle.prototype.on = function (event, listener) {
    if(this.emitter) this.emitter.on(event, listener);
    return this;
};

/**
 * End configuration and Attach events
 * @name MenuToggle#init
  */
MenuToggle.prototype.init= function()
{
    let itemListener, firstItem,
        i=0,
        $this= this,
        menuItemsNodeList = this.getMenuItemsNodeList(),
        popstateEvent ={
            handleEvent: function (event) {
                let currentHistorySelector= $this.menuContainer.querySelector($this.menuItemsSelector+'[href="'+window.location.hash+'"]');
                $this.show(currentHistorySelector);
            }
        };

    for(; i<menuItemsNodeList.length; i++){
        itemListener = {
            el: menuItemsNodeList[i],
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

        menuItemsNodeList[i].addEventListener('click', itemListener, false);
    }

    // navigate to a tab when the history changes
    window.addEventListener("popstate", popstateEvent);

    //Open div from hash || first active selector || first item in menu
    firstItem=  (window.location.hash)?
        this.menuContainer.querySelector(this.menuItemsSelector+'[href="'+window.location.hash+'"]') :
        this.menuContainer.querySelector(this.menuItemsSelector+'.'+this.activeSelector);

    if(firstItem){
        firstItem.click();
    }else{
        menuItemsNodeList[0].click();
    }

};

/**
 * Load div
 * @param {Object} menuItem Menu link
 */
MenuToggle.prototype.show= function(menuItem)
{
    let i=0,
        previous= this.menuContainer.querySelector('.'+this.activeSelector),
        selectedTarget  = document.querySelector(menuItem.hash),
        datatargets = this.getDataTargets()
    ;

    if(typeof selectedTarget === "undefined")
    {
        console.log('Target hash ID desconocido: '+ menuItem.hash);
        return false;
    }


    if(previous) previous.classList.remove(this.activeSelector);

    menuItem.classList.add(this.activeSelector, this.visitedSelector);

    for (; i < datatargets.length; i++)
    {
        if(datatargets[i] ===  selectedTarget) continue;

        datatargets[i].style.display = 'none';

        if(this.emitter)
        {
            /**
             * Hide event for for all menu elements
             * @example menuToggle.on('click.dataTargetId', function(menuItem, dataTarget){})
             */
            this.emitter.emit('hide', datatargets[i]);
            /**
             * Hide event for this menu element
             * @example menuToggle.on('hide.dataTargetId', function(dataTarget){})
             */
            this.emitter.emit('hide.' + datatargets[i].id, datatargets[i]);
        }
    }

    selectedTarget.style.display= 'inline';

    if(this.emitter)
    {
        /**
         * Show event for this menu element
         * @example menuToggle.on('hide.dataTargetId', function(dataTarget){})
         */
        this.emitter.emit('show.'+selectedTarget.id, selectedTarget);
    }
};