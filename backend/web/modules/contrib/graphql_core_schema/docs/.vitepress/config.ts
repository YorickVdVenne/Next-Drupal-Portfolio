import { defineConfig } from "vitepress";

const ICON_DRUPAL = `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><title>drupal</title><path d="M20.47,14.65C20.47,15.29 20.25,16.36 19.83,17.1C19.4,17.85 19.08,18.06 18.44,18.06C17.7,17.95 16.31,15.82 15.36,15.72C14.18,15.72 11.73,18.17 9.71,18.17C8.54,18.17 8.11,17.95 7.79,17.74C7.15,17.31 6.94,16.67 6.94,15.82C6.94,14.22 8.43,12.84 10.24,12.84C12.59,12.84 14.18,15.18 15.36,15.08C16.31,15.08 18.23,13.16 19.19,13.16C20.15,12.95 20.47,14 20.47,14.65M16.63,5.28C15.57,4.64 14.61,4.32 13.54,3.68C12.91,3.25 12.05,2.3 11.31,1.44C11,2.83 10.78,3.36 10.24,3.79C9.18,4.53 8.64,4.85 7.69,5.28C6.94,5.7 3,8.05 3,13.16C3,18.27 7.37,22 12.05,22C16.85,22 21,18.5 21,13.27C21.21,8.05 17.27,5.7 16.63,5.28Z" /></svg>`;

// https://vitepress.dev/reference/site-config
export default defineConfig({
  title: "GraphQL Core Schema",
  description: "Drupal Core Schema Generator",
  themeConfig: {
    // https://vitepress.dev/reference/default-theme-config
    nav: [
      { text: "Home", link: "/" },
      { text: "Guide", link: "/basics/setup" },
      {
        text: "drupal.org",
        link: "https://www.drupal.org/project/graphql_core_schema",
      },
    ],

    sidebar: [
      {
        text: "Basics",
        items: [
          { text: "Setup", link: "/basics/setup" },
          { text: "Configuration", link: "/basics/configuration" },
          { text: "Architecture", link: "/basics/architecture" },
          { text: "Security", link: "/basics/security" },
          {
            text: "Comparison with graphql_core",
            link: "/basics/comparison-with-graphql_core",
          },
          { text: "Troubleshooting", link: "/basics/troubleshooting" },
        ],
      },
      {
        text: "Schema",
        items: [
          { text: "Base", link: "/schema/base" },
          {
            text: "Entity & Field",
            link: "/schema/entity-field",
          },
        ],
      },
      {
        text: "Schema Extensions",
        items: [
          { text: "Breadcrumb", link: "/schema-extensions/breadcrumb" },
          { text: "Debugging", link: "/schema-extensions/debugging" },
          { text: "Entity Query", link: "/schema-extensions/entity-query" },
          { text: "Formatted Date", link: "/schema-extensions/formatted-date" },
          { text: "Image", link: "/schema-extensions/image" },
          {
            text: "Language Switch Links",
            link: "/schema-extensions/language-switch-links",
          },
          { text: "Local Tasks", link: "/schema-extensions/local-tasks" },
          { text: "Media", link: "/schema-extensions/media" },
          { text: "Menu", link: "/schema-extensions/menu" },
          {
            text: "Render Field Item",
            link: "/schema-extensions/render-field-item",
          },
          {
            text: "Reverse Entity Reference",
            link: "/schema-extensions/reverse-entity-reference",
          },
          { text: "Routing", link: "/schema-extensions/routing" },
          { text: "Taxonomy", link: "/schema-extensions/taxonomy" },
          { text: "User", link: "/schema-extensions/user" },
          { text: "User Login", link: "/schema-extensions/user-login" },
          { text: "Views", link: "/schema-extensions/views" },
        ],
      },
      {
        text: "Modules",
        items: [
          { text: "Messenger", link: "/modules/messenger" },
          {
            text: "Environment Indicator",
            link: "/modules/environment-indicator",
          },
          { text: "Metatag", link: "/modules/metatag" },
          { text: "Masquerade", link: "/modules/masquerade" },
          { text: "Rokka", link: "/modules/rokka" },
          { text: "Tablefield", link: "/modules/tablefield" },
          { text: "Telephone", link: "/modules/telephone" },
        ],
      },
      {
        text: "Advanced",
        items: [
          {
            text: "Extending Interfaces",
            link: "/advanced/extending-interfaces",
          },
        ],
      },
    ],

    socialLinks: [
      {
        icon: {
          svg: `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><title>git</title><path d="M2.6,10.59L8.38,4.8L10.07,6.5C9.83,7.35 10.22,8.28 11,8.73V14.27C10.4,14.61 10,15.26 10,16A2,2 0 0,0 12,18A2,2 0 0,0 14,16C14,15.26 13.6,14.61 13,14.27V9.41L15.07,11.5C15,11.65 15,11.82 15,12A2,2 0 0,0 17,14A2,2 0 0,0 19,12A2,2 0 0,0 17,10C16.82,10 16.65,10 16.5,10.07L13.93,7.5C14.19,6.57 13.71,5.55 12.78,5.16C12.35,5 11.9,4.96 11.5,5.07L9.8,3.38L10.59,2.6C11.37,1.81 12.63,1.81 13.41,2.6L21.4,10.59C22.19,11.37 22.19,12.63 21.4,13.41L13.41,21.4C12.63,22.19 11.37,22.19 10.59,21.4L2.6,13.41C1.81,12.63 1.81,11.37 2.6,10.59Z" /></svg>`,
        },
        link: "https://git.drupalcode.org/project/graphql_core_schema",
      },
      {
        icon: {
          svg: ICON_DRUPAL,
        },
        link: "https://www.drupal.org/project/graphql_core_schema",
      },
    ],
  },
});
