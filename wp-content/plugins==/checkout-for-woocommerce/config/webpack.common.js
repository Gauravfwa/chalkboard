// Imports
const MiniCssExtractPlugin = require( 'mini-css-extract-plugin' );
const { CleanWebpackPlugin } = require( 'clean-webpack-plugin' );

module.exports = ( sourcesDir ) => ( {
  externals: {
    jquery: 'jQuery',
  },
  entry: {
    'checkoutwc-vendor': [
      `${sourcesDir}/js/vendor.js`,
    ],
    'checkoutwc-front': [
      `${sourcesDir}/ts/checkout.ts`,
      `${sourcesDir}/scss/front/checkout.scss`,
    ],
    'checkoutwc-order-pay': [
      `${sourcesDir}/ts/order-pay.ts`,
      `${sourcesDir}/scss/front/order-pay.scss`,
    ],
    'checkoutwc-thank-you': [
      `${sourcesDir}/ts/thank-you.ts`,
      `${sourcesDir}/scss/front/thank-you.scss`,
    ],
  },
  resolve: {
    extensions: ['.ts', '.js', '.json', '.scss'],
  },
  stats: {
    colors: true,
  },
  module: {
    rules: [
      {
        test: /\.ts$/,
        loader: ['ts-loader'],
      },
      {
        test: /\.(scss|css)$/,
        use: [
          MiniCssExtractPlugin.loader,
          {
            loader: 'css-loader',
            options: {
              sourceMap: true,
              minimize: false,
              url: false,
            },
          },
          {
            loader: 'postcss-loader',
            options: {
              sourceMap: true,
            },
          },
          {
            loader: 'sass-loader',
            options: {
              sourceMap: true,
            },
          },
        ],
      },
    ],
  },
  plugins: [
    new CleanWebpackPlugin(),
  ],
} );
