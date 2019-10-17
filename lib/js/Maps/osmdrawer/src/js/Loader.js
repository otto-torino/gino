import Dispatcher from 'EventDispatcher'

/**
 * Library loader
 *
 * <p>This library requires a MapBox token and jQuery, this loader assures that token is available
 * and loads jQuery from cdn if not already loaded in the document. When requirements
 * are satisfied the callback passed to the {@link osmloader#ready} method is invoked</p>
 * @namespace
 */
const Loader = {
  /**
   * Loads the library and emits an event when loaded
   * The event emitted is 'onDrawerLoader'
   * @memberof Loader
   */
  load: function () {
    this.checkToken()
    this.requirejQuery(() => {
      console.info('osmdrawer: library is ready')
      Dispatcher.emit('onDrawerLoaded')
    })
  },
  /**
   * Checks if the MapBox token in set
   * @memberof Loader
   */
  checkToken: function () {
    if (!window.OSM_TOKEN) {
      console.info('open-street-map-drawer: window.OSM_TOKEN must be set at this point!')
      throw new Error('Missing MapBox token')
    }
  },
  /**
   * Checks if jQuery is loaded, if not loads it, and then execute the cb
   * @memberof Loader
   * @param {Function} then callback to execute after jQuery is fully loaded
   */
  requirejQuery (then) {
    if (window.jQuery === undefined) {
      console.info('osmdrawer: loading jQuery')
      let script = document.createElement('script')
      script.type = 'text/javascript'
      if (script.readyState) {  // IE
        script.onreadystatechange = function () {
          if (script.readyState === 'loaded' || script.readyState === 'complete') {
            script.onreadystatechange = null
            then.call(this)
          }
        }
      } else {  // Others
        script.onload = function () {
          then.call(this)
        }
      }
      script.src = 'https://ajax.googleapis.com/ajax/libs/jquery/3.0.0/jquery.min.js'
      document.getElementsByTagName('head')[0].appendChild(script)
    } else {
      then()
    }
  }
}

export default Loader
