import 'babel-polyfill';
import 'leaflet-defaulticon-compatibility'
import 'Leaflet.Editable'
import Dispatcher from 'EventDispatcher'
import Loader from 'Loader'
import Map from 'Map'
import PointTool from 'PointTool'
import PolylineTool from 'PolylineTool'
import PolygonTool from 'PolygonTool'
import CircleTool from 'CircleTool'

require('../scss/base.scss')

/**
 * <h2>open-street-map-drawer module</h2>
 *
 * <p>This is a global object, attached to the window object, it exports all the classes except from the Tool one.
 * Provides a ready method which receive as first argument the callback to invoke when the library is fully loaded</p>
 * <p>It is also exported as es2016 module.</p>
 *
 * @property {Map} Map {@link Map}
 * @property {PointTool} PointTool {@link PointTool}
 * @property {PolygonTool} PolylineTool {@link PolylineTool}
 * @property {PolygonTool} PolygonTool {@link PolygonTool}
 * @property {CircleTool} CircleTool {@link CircleTool}
 * @property {Loader} Loader {@link Loader}
 * @property {EventDispatcher} EventDispatcher {@link EventDispatcher}
 *
 * @module osmdrawer
 */
window.OpenStreetMapDrawer = {
  /**
   * Loads the library and executes the given callback only when the it is ready
   * @memberof module:osmdrawer
   * @param {Function} callback
   * @return void
   */
  ready: function (callback) {
    Dispatcher.register('onDrawerLoaded', function () {
      callback.call(this)
    })
    Loader.load()
  },
  Map: Map,
  PointTool: PointTool,
  PolylineTool: PolylineTool,
  PolygonTool: PolygonTool,
  CircleTool: CircleTool,
  Loader: Loader,
  EventDispatcher: Dispatcher
}

export default window.OpenStreetMapDrawer
