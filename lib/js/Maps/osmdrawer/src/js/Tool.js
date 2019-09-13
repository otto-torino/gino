import {sprintf} from 'utils'

/**
 * Drawing tool class.
 *
 * <p>This class is the superclass for all tools, extended by all specific tools.</p>
 * <p><b>DO NOT INSTANTIATE THIS CLASS DIRECLTY</b>, use its children instead.</p>
 *
 * @abstract
 *
 */
const Tool = class {

  /**
   * @summary Constructs a Tool object
   *
   * @param {Map} map The Map instance which handles the tool
   * @param {String|Element} ctrl The selector or the element itself which controls the tool when clicking over it,
   *                         set to null to have the default controller
   * @param {String} toolName The drawing tool name
   */
  constructor (map, ctrl, toolName) {
    this._state = {
      active: false,
      items: [] // array storing all the drawed items
    }
    this._map = null
    this._ctrl = null
    // store the ctrl given, will be used when the tool is activated.
    this._ctrlParam = ctrl
    this._toolName = null

    this._map = map
    this._toolName = toolName

    // next click has to begin a new shape?
    this._nextShape = false

    this._options = {
      maxItemsAllowed: 1
    }
  }

  /**
   * @summary Sets the tool controller
   * @ignore
   * @param {String/Element} [ctrl=null]
   *    The selector or jQuery element which serves as the tool controller,
   *    if <code>null</code> the default controller is used.
   * @return void
   */
  _setController (ctrl) {
    if (ctrl) {
      this._ctrl = jQuery(ctrl)
      if (!this._ctrl.length) {
        throw new Error(sprintf('the given ctrl for the {0} tool is not a DOM element', this._toolName))
      }
    } else {
      // default
      this._ctrl = jQuery('<div />', {
        'class': 'osmdrawer-ctrl-' + this._toolName + '-tool',
        title: this._toolName + ' tool'
      })
      this._map.addDefaultCtrl(this._ctrl)
    }
  }

  /**
   * @summary Removes the default tool controller
   * @ignore
   * @return void
   */
  _removeController () {
    this._ctrl.remove()
    this._ctrl = null
  }

  // PUBLIC METHODS (to be intended as public ;)

  /**
   * @summary Returns the tool name
   * @return {String} The tool name
   */
  getToolName () {
    return this._toolName
  }

  /**
   * @summary Adds an item to the items
   * @param {Object} item a map shape
   * @return void
   */
  addItem (item) {
    this._state.items.push(item)
  }

  /**
   * @summary Sets the maximum number of items that the tool may draw
   * @param max The maximum number of drawable items
   * @return void
   */
  setMaxItemsAllowed (max) {
    this._options.maxItemsAllowed = parseInt(max, 10)
  }

  /**
   * @summary Sets the value of the next shape property (a new click starts a new shape if true)
   * @param enable Whether or not next click has to start a new shape
   * @return void
   */
  setNextShape (enable) {
    this._nextShape = !!enable
  }

  /**
   * @summary Activates the tool
   * @return {Tool} instance
   */
  activate () {
    this._state.active = true

    this._setController(this._ctrlParam)

    this._ctrl.on('click', this.setDrawing.bind(this))
    this._ctrl.removeClass('osmdrawer-ctrl-inactive')
    this._ctrl.addClass('osmdrawer-ctrl-active')

    console.info(sprintf('osmdrawer: {0} tool activated', this._toolName))

    return this
  }

  /**
   * @summary Removes the tool
   * @param {Boolean} [removeCtrl=false] Whether or not to remove the tool control if the default one
   * @return {Tool} instance
   */
  deactivate (removeCtrl = false) {
    if (this._state.active) {
      this._state.active = false
      this._ctrl.removeClass('osmdrawer-ctrl-active')
      this._ctrl.addClass('osmdrawer-ctrl-inactive')
      // @TODO check me!
      this._ctrl.off('click', null, this.setDrawing)

      if (this._map.getDrawingTool() === this) {
        this._map.setDrawingTool(null)
      }

      if (removeCtrl && this._ctrlParam == null) {
        this._removeController()
      }

      console.info(sprintf('{0} tool deactivated', this._toolName))
    } else {
      if (removeCtrl && this._ctrlParam === null) {
        this._removeController()
      }
      console.info(sprintf('{0} tool already deactivated', this._toolName))
    }
    return this
  }

  /**
   * @summary Sets the current drawing tool
   * @return {Tool} instance
   */
  setDrawing () {
    this.prepareTool()
    this._map.updateTips(this.tipsText())
    console.info('osmdrawer: drawing tool: ' + this._toolName)
    this._map.setDrawingTool(this)
    return this
  }

  /**
   * @summary Prepares the current drawing tool
   * @description Empty because at the moment has to do nothing, but it's a place where some things
   *              can be done in the future, I suppose.
   * @return {Tool} instance
   */
  prepareTool () {}

  /**
   * @summary Sets the css selected class
   * @return void
   */
  setSelected () {
    this._ctrl.addClass('osmdrawer-selected')
    return this
  }

  /**
   * @summary Removes the css selected class
   * @return {Tool} instance
   */
  setUnselected () {
    this._ctrl.removeClass('osmdrawer-selected')
    return this
  }
}

export default Tool
