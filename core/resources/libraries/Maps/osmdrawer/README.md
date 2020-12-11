# OpenStreetMapDrawer

Hello folks, this is a js library designed to let an user easily draw shapes over an open street map and import/export them in order to make something useful, i.e. save them to a database engine.

It uses the [leaflet](https://leafletjs.com/) library with the [mapbox](https://www.mapbox.com/) tiles.

Its simplest usage scenario is when you need to geolocalize some entities and save such info in the database, along with all other stuff (name, description and so on...)

It was designed with the simplicity of usage and integration as primary focus, so that it can be integrated and used in any web application backoffice with no pain:

- no css files involved
- no static assets involved
- just source a js file and instantiate the library

## Features

Some features:

- draw shapes over the map: points, polylines, polygons, circles.
- set a maximum number of drawable shapes per type
- use the geodecoder service to center the map or draw a point
- clear or export the map (the drawn shapes)
- fullscreen functionality
- responsive
- import shapes and edit them before exporting again

## Usage

### Installation

Just download or clone the repo

    $ git clone https://github.com/otto-torino/osmdrawer.git

or use bower ;)

    $ bower install osmdrawer

### How to

Include the min library (`dist/osmdrawer.min.js`) in the head of your document or in the body, or load it async, it's up to you. Make sure to define earlier a global window prop called `OSM_TOKEN` containing the mapbox access token:

    <script type="text/javascript">window.OSM_TOKEN = 'MYTOKEN';</script>
    <script type="text/javascript" src="bower_components/osmdrawer/dist/osmdrawer.min.js"></script>

Define the map canvas container somewhere in the document:

    <div id="map-canvas"></div>

Instantiate the OpenStreetMapDrawer.Map class passing some options, then call its render method. Do all this inside a callback function passed to `OpenStreetMapDrawer.ready()`, in order to be sure the library is fully loaded:

    <script>
      var cb = function () {
        var mymap = new OpenStreetMapDrawer.Map('#map-canvas', {
          tools: {
            point: {
              options: {
                maxItemsAllowed: 3,
              }
            },
            polyline: {},
            polygon: {},
            circle: {}
          },
          exportMapCb: function (data) {
            console.log('exported data: ', data);
          }
        });
        mymap.render();
      }
      OpenStreetMapDrawer.ready(cb);
    </script>

Need to import existing data?

    ...
    mymap.render()
    mymap.importMap({
      point: [{lat: 45, lng: 7}],
      polyline: [
        [{lat: 44, lng: 7}, {lat: 44.3, lng: 7.2}]
      ]
    });
    ...

The library provides some classes:

- Loader (checks google maps API an loads jquery on the fly if no yet loaded)
- EventDispatcher (a mediator like event dispatcher)
- Map (the main class)
- Tool (something like an abstract class which every tool extends)
- PointTool (markers)
- PolylineTool (polylines)
- PolygonTool (polygons)
- CircleTool (circles)

Each class has its own public methods (actually are all public in js, but some of them are not meant to be, and they start with an underscore char).

Every tool can be customize at runtime, you can add or remove tools, change options and so on...

#### Need to use custom controllers?

If you have already an interface designed with buttons to export data or clear the map, that's not a problem, you can provide your own controllers for all the tools and almost all the functionalities; the library will manage (attach and detach) the events itself. The active controller receives a css class `osmdrawer-selected` so that you can manage its active state.

## Development

The repository comes with a development environment involving:

- node
- webpack
- babel

The code is written following the es2015, es2016 and es2017 standards, babel is smart enough to produce a browser compatible bundle.

Get started by cloning the repository and running

    $ npm install

To start development:

    $ npm run dev

A server is started on http://localhost/8080, which serves the root directory. No need to run it again, changes in files are detected and the bundle is re-generated automatically, just reload the page.

To compile for production:

    $ npm run compile

To generate the library reference:

    $ npm run docs


## License

[MIT License](https://opensource.org/licenses/MIT)

## Credits

[OTTO srl](http://www.otto.to.it) - [abidibo](http://www.abidibo.net)
