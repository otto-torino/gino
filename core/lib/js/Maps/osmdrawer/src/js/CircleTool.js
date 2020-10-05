import L from 'leaflet'
import Tool from 'Tool'

/**
 * Open Street Map maps drawing circle tool class. Provides methods to draw over the {@link Map} instance
 *
 * <p>The circle drawing tool class, which allows to draw circles over the gmapdraw map instance.</p>
 * @extends Tool
 */
const CircleTool = class extends Tool {
  /**
   * Constrcuts a circle tool
   * @param {Map} map The map instance which handles the tool
   * @param {String|Object} ctrl The selector or the jQuery element which controls the tool when clicking over it,
   *                        set to null to have the default controller
   * @param {Object} options A class options object
   * @param {Number} [options.maxItemsAllowed=1] The maximum number of shapes the tool may draw.
   */
  constructor (map, ctrl, options) {
    super(map, ctrl, 'circle')

    this._drawingCircle = null
    this._mapMoveListener = null
    this._circleMoveListener = null

    this._options = jQuery.extend({}, this._options, options)
  }

  /**
   * @summary Returns the tool help tip text
   * @return {String} The tips text
   */
  tipsText () {
    return 'Click on the map to add circles. Right click on existing circles to delete them.'
  }

  /**
   * @summary Prepares the tool
   * @return void
   */
  prepareTool () {
    super.prepareTool()
    this._nextShape = true
  }

  /**
   * @summary Handles the click event over the map when the tool is the drawing one
   * @param {MouseEvent} evt
   * @return void
   */
  clickHandler (evt) {
    // if next shape && maximum shape number is not reached
    if (this._nextShape && this._state.items.length < this._options.maxItemsAllowed) {
      let circle = L.circle(evt.latlng, {
        radius: 10000
      }).addTo(this._map.gmap())
      circle.enableEdit()
      this._drawingCircle = true

      this._state.items.push(circle)

      // right click to delete one
      circle.on('contextmenu', () => {
        circle.remove()
        this._state.items.splice(this._state.items.indexOf(circle), 1)
        this._nextShape = true // otherwise next click will populate the last polyline
      })
    } else if (!this._drawingCircle && this._state.items.length >= this._options.maxItemsAllowed) {
      console.info('maximum number of circles reached')
      alert('Maximum number of insertable circles reached')
      return null
    } else {
      // currently drawing
      this._drawingCircle = false
    }
  }

  /**
   * @summary Clears all circles
   * @return void
   */
  clear () {
    this._state.items.forEach((circle) => {
      circle.remove()
    })
    this._state.items = []
    console.info('circles cleared')
  }

  /**
   * @summary Returns the distance between 2 LatLng points
   * @param {LatLng} point1 The first point
   * @param {LatLng} point2 The second point
   * @return {Number} The distance in meters
   */
  distance (point1, point2) {
    let R = 6371000 // earth's radius in meters
    let dLat = (point2.lat() - point1.lat()) * Math.PI / 180
    let dLon = (point2.lng() - point1.lng()) * Math.PI / 180
    let a = Math.sin(dLat / 2) * Math.sin(dLat / 2) +
      Math.cos(point1.lat() * Math.PI / 180) * Math.cos(point2.lat() * Math.PI / 180) *
      Math.sin(dLon / 2) * Math.sin(dLon / 2)
    let c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a))
    let d = R * c

    return d
  }

  /**
   * @summary Returns all the drawn circles data
   * @return {Array} data An array objects representing the circle's properties
   * @example
   *    // exported data
   *    [{lat: 45, lng: 7, radius: 40000}, {lat: 35, lng: 15, radius: 650000}]
   */
  exportData () {
    let data = this._state.items.map(
      (circle) => ({lat: circle.getLatLng().lat, lng: circle.getLatLng().lng, radius: circle.getRadius()})
    )
    return data
  }

  /**
   * @summary Imports all data as circles
   * @param {Array} data An array objects representing the circle's properties
   */
  importData (data) {
    for (let i = 0; i < data.length; i++) {
      let circle = data[i]
      let dcircle = L.circle(L.latLng(circle.lat, circle.lng), {
        radius: circle.radius
      }).addTo(this._map.gmap())
      dcircle.enableEdit()

      this.addItem(dcircle)
    }
  }

  /**
   * @summary Extends the map bounds to fit the circles
   * @param {LatLngBounds} [bounds] the LatLngBounds object
   */
  extendBounds (bounds) {
    this._state.items.forEach((circle) => {
      bounds.extend(circle.getBounds())
    })
  }
}

export default CircleTool
