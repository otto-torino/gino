import webpack from 'webpack'
import path from 'path'

const webpackConfig = {
  node: {
    fs: 'empty'
  },
  // this is needed to resolve imports from the js root
  resolve: {
    // always import from root (src/js and node_modules)
    root: [
      'src/js',
      'node_modules'
    ],
    extensions: ['', '.js', '.json']
  },
  resolveLoader: {
    root: path.join(__dirname, 'node_modules')
  },
  // library entry point
  entry: './src/js/index.js',
  output: {
    path: path.join(__dirname, 'dist'),
    publicPath: '/dist',
    filename: 'osmdrawer.min.js'
  },
  // this for creating source maps
  devtool: 'source-map'
}

webpackConfig.plugins = [
  new webpack.optimize.UglifyJsPlugin({minimize: true})
]

webpackConfig.module = {
  loaders: []
}

// js loaders
webpackConfig.module.loaders.push({
  test: /\.(js)$/,
  exclude: /node_modules/,
  loader: 'babel-loader',
  query: {
    presets: ['es2015', 'stage-0']
  }
})

// css loaders
webpackConfig.module.loaders.push({
  test: /\.scss/,
  loader: 'style!css!sass'
})

webpackConfig.module.loaders.push({
  test: /\.css/,
  loader: 'style!css'
})

// json loaders
webpackConfig.module.loaders.push({
  test: /\.json$/,
  loader: 'json'
})

// File loaders
webpackConfig.module.loaders.push(
  {
    test: /\.svg(\?.*)?$/,
    loader: 'url?prefix=fonts/&name=[path][name].[ext]&limit=10000&mimetype=image/svg+xml'
  },
  {
    test: /\.(png|jpg)$/,
    loader: 'url?limit=8192'
  }
)

export default webpackConfig
