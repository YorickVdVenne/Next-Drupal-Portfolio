const path = require('path')

module.exports = {
  i18n: {
    defaultLocale: 'en-US',
    locales: ['en-US', 'nl-NL'],
    localeDetection: true
  },
  localePath: path.resolve('./public/locales')
}
