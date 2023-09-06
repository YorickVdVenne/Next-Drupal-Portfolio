# Menu

## Schema

### Base

<<< @/../graphql/menu.base.graphqls{graphql}

### Extension

<<< @/../graphql/menu.extension.graphqls{graphql}

### Enum

The `MenuName` enum is generated based on the menus that exist in Drupal, e.g.:

```graphql
enum MenuName {
  ADMIN
  FOOTER
  MAIN
  TOOLS
}
```

## Example

```graphql
query mainMenu {
  menuByName(name: ADMIN) {
    links {
      ...mainMenuLinkTree
      subtree {
        ...mainMenuLinkTree
      }
    }
  }
}

fragment mainMenuLinkTree on MenuLinkTreeElement {
  link {
    label
    url {
      path
    }
  }
}
```

```json
{
  "data": {
    "menuByName": {
      "links": [
        {
          "link": {
            "label": "Verwaltung",
            "url": {
              "path": "/de/admin"
            }
          },
          "subtree": [
            {
              "link": {
                "label": "Werkzeuge",
                "url": {
                  "path": "/de"
                }
              }
            },
            {
              "link": {
                "label": "Inhalt",
                "url": {
                  "path": "/de/admin/content"
                }
              }
            },
            {
              "link": {
                "label": "Struktur",
                "url": {
                  "path": "/de/admin/structure"
                }
              }
            },
            {
              "link": {
                "label": "Design",
                "url": {
                  "path": "/de/admin/appearance"
                }
              }
            },
            {
              "link": {
                "label": "Erweitern",
                "url": {
                  "path": "/de/admin/modules"
                }
              }
            },
            {
              "link": {
                "label": "Konfiguration",
                "url": {
                  "path": "/de/admin/config"
                }
              }
            },
            {
              "link": {
                "label": "Benutzer",
                "url": {
                  "path": "/de/admin/people"
                }
              }
            },
            {
              "link": {
                "label": "Berichte",
                "url": {
                  "path": "/de/admin/reports"
                }
              }
            },
            {
              "link": {
                "label": "Help",
                "url": {
                  "path": "/de/admin/help"
                }
              }
            }
          ]
        }
      ]
    }
  }
}
```
