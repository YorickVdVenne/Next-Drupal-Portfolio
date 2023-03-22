const path = require("path");
const TsconfigPathsPlugin = require("tsconfig-paths-webpack-plugin");

module.exports = {
  core: {
    builder: "webpack5",
  },
  staticDirs: ["../public"],
  stories: ["../**/*.stories.mdx", "../**/*.stories.@(js|jsx|ts|tsx)"],
  addons: [
    {
      name: "storybook-addon-next",
      options: {
        nextConfigPath: path.resolve(__dirname, "../next.config.js"),
      },
    },
    "@storybook/addon-links",
    "@storybook/addon-essentials",
    "@storybook/manager-webpack5",
    "storybook-addon-next-router",
  ],
  webpackFinal: async (config) => {
    const fileLoaderRule = config.module.rules.find(
      (rule) => rule.test && rule.test.test(".svg")
    );
    fileLoaderRule.exclude = /\.svg$/;
    return ({
      ...config,
      resolve: {
        ...config.resolve,
        plugins: [
          new TsconfigPathsPlugin()
        ],
        alias: {
          ...config.resolve.alias,
          'next-i18next': 'react-i18next',
          "@api/": path.resolve(__dirname, "../api/*"),
          "@components/": path.resolve(__dirname, "../components/*"),
          "@white-label/*": path.resolve(__dirname, "../themes/whitelabel/*"),
          "@generated/": path.resolve(__dirname, "../generated/*"),
          "@pages/*": path.resolve(__dirname, "../pages/*"),
          "@lib/*": path.resolve(__dirname, "../lib/*"),
          "@misc/*": path.resolve(__dirname, "../misc/*"),
          "@themes/*": path.resolve(__dirname, "../themes/*"),
        },
      },
      module: {
        ...config.module,
        rules: [
          ...config.module.rules,
          {
            test: /\.svg$/,
            enforce: "pre",
            use: [
              {
                loader: require.resolve('@svgr/webpack'),
                options: {
                  svgoConfig: {
                    plugins: {
                      removeViewBox: false
                    }
                  }
                }
              }
            ]
          }
        ]
      }
    })
  }
}