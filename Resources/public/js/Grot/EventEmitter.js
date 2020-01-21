/* Polyfill EventEmitter. */
function EventEmitter() {
    this.listeners = {};
};

/**
 * @name EventEmitter#on
 * @method
 * @param {string} event
 * @param {function} listener
 */
EventEmitter.prototype.on = function (event, listener) {
    if (typeof this.listeners[event] !== 'object') {
        this.listeners[event] = [];
    }
    this.listeners[event].push(listener);
    return this;
};
/**
 * @name EventEmitter#removeListener
 * @method
 * @param {string} event
 * @param {function} listener
 */
EventEmitter.prototype.removeListener = function (event, listener) {
    let lis = this.listeners[event];
    if (!lis) return this;
    for(let i = lis.length; i > 0; i--) {
        if (lis[i] === listener) {
            lis.splice(i,1);
            break;
        }
    }
    return this;
};
/**
 * Shortcut to removeListener
 * @name EventEmitter#off
 * @method
 * @param {string} event
 * @param {function} listener
 */
EventEmitter.prototype.off = function (event, listener) {
    return this.removeListener(event, listener);
};
/**
 * Call function/s for event
 * @name EventEmitter#emit
 * @method
 * @param {string} event
 * @param {function} listener
 * return {array} Listener return data if any
 */
EventEmitter.prototype.emit = function (event) {
    let data= [], result, i, listeners, length, args = [].slice.call(arguments, 1);

    if (typeof this.listeners[event] === 'object') {
        listeners = this.listeners[event].slice();
        length = listeners.length;

        for (i = 0; i < length; i++) {
            result = listeners[i].apply(this, args);
            if(typeof result!== "undefined") data.push(result);
        }
        return data;
    }
};
/**
 * Call one listener for event
 * @name EventEmitter#once
 * @method
 * @param {string} event
 * @param {function} listener
 */
EventEmitter.prototype.once = function (event, listener) {
    this.on(event, function g () {
        this.removeListener(event, g);
        listener.apply(this, arguments);
    });
};

