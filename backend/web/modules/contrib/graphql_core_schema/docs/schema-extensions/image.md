# Image

Additional fields for images, like getting image derivatives.

## Schema

### Base

<<< @/../graphql/image.base.graphqls{graphql}

The extension also automatically generates an Enum called `ImageStyleId` that contains all available image styles, for example:

```graphql
enum ImageStyleId {
  """
  Hero
  """
  HERO

  """
  Linkit result thumbnail
  """
  LINKIT_RESULT_THUMBNAIL

  """
  Wide (1090)
  """
  WIDE
}
```

### Extension

<<< @/../graphql/image.FieldItemTypeImage.graphqls{graphql}

## Example

```graphql
query {
  entityById(entityType: MEDIA, id: "3") {
    ... on MediaImage {
      fieldImage {
        hero: derivative(style: HERO) {
          urlPath
          width
          height
        }
        thumbnail: derivative(style: THUMBNAIL) {
          urlPath
          width
          height
        }
      }
    }
  }
}
```

```json
{
  "data": {
    "entityById": {
      "fieldImage": {
        "hero": {
          "urlPath": "https://foobar.rokka.io/hero/ee66377163af41440809d8c3ec17ba82cb0a3a62/test.jpg",
          "width": 1344,
          "height": 672
        },
        "thumbnail": {
          "urlPath": "https://foobar.rokka.io/thumbnail/ee66377163af41440809d8c3ec17ba82cb0a3a62/test.jpg",
          "width": 100,
          "height": 100
        }
      }
    }
  }
}
```
