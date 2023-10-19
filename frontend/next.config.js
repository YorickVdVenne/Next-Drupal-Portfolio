const { i18n } = require('./next-i18next.config')
const withTranslateRoutes = require('next-translate-routes/plugin')

module.exports = withTranslateRoutes({
  i18n,
  translateRoutes: {
    debug: true
  },
  webpack (config) {
    config.module.rules.push({
      test: /\.svg$/,
      use: [
        {
          loader: '@svgr/webpack',
          options: {
            svgoConfig: {
              plugins: [
                {
                  name: 'preset-default',
                  params: {
                    overrides: {
                      removeViewBox: false
                    }
                  }
                }
              ]
            }
          }
        }
      ]
    })

    return config
  }
})
