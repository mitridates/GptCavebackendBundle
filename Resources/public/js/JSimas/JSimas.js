'use strict';

/**
 *
 * @constructor
 * @name JSimas
 * @param {String|Object} selector
 */
function JSimas (selector) {
    if (!(this instanceof JSimas)) return new JSimas(selector)
    this._elements = (typeof selector === 'string') ? document.querySelectorAll(selector) : selector;
}