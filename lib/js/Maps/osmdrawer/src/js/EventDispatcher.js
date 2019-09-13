/**
 * Event Dispatcher
 *
 * Implementation of an event dispatcher following the Mediator pattern
 * @namespace
 */
const EventDispatcher = {
  _prefix: 'on_',
  _listeners: {},
  /**
   * @summary Adds a prefix to the event name
   * @description Assures that event name doesn't match a standard Object property name
   * @param {String} evtName event name
   * @return {String} prefixed event name
   */
  evtName: function (evtName) {
    return this._prefix + evtName
  },
  /**
   * @summary Registers a callback to an event
   * @param {String} evtName name of the event
   * @param {Function} callback function binded to the event
   * @param {Mixed} bind the value of this provided to the callback
   * @return void
   */
  register: function (evtName, callback, bind) {
    var _evtName = this.evtName(evtName)
    if (typeof this._listeners[_evtName] === 'undefined') {
      this._listeners[_evtName] = []
    }
    this._listeners[_evtName].push([!bind ? this : bind, callback])
  },
  /**
   * @summary Unregisters one or all callbacks binded to the given event
   * @param {String} evtName name of the event
   * @param {Function} callback function to unregister. All callbacks if empty
   * @return void
   */
  unregister: function (evtName, callback) {
    var _evtName = this.evtName()
    if (typeof callback === 'undefined') {
      delete this._listeners[_evtName]
    } else {
      for (var i = 0, len = this._listeners[_evtName].length; i < len; i++) {
        var listener = this._listeners[_evtName][i]
        if (listener[1] === callback) {
          this._listeners[_evtName].splice(i, 1)
        }
      }
    }
  },
  /**
   * @summary Emits an event, all registered callbacks are called
   * @param {String} evtName name of the event
   * @param {Mixed} params additional parameters passed to the callback
   */
  emit: function (evtName, params) {
    var _evtName = this.evtName(evtName)
    if (typeof this._listeners[_evtName] !== 'undefined') {
      for (var i = 0, l = this._listeners[_evtName].length; i < l; i++) {
        this._listeners[_evtName][i][1].call(this._listeners[_evtName][i][0], evtName, params)
      }
    }
  }
}

export default EventDispatcher
