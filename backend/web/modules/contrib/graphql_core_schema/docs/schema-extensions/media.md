# Media

Additional fields for media entities.

## Schema

### Extension

```graphql
extend interface Media {
  mediaFileUrl: Url
}
```

## `mediaFileUrl`

This field is added to the `Media` interface and allows you to get the file URL of a media entity.

```graphql
query {
  entityById(entityType: MEDIA, id: 5) {
    ... on Media {
      mediaFileUrl {
        path
      }
    }
  }
}
```

```json
{
  "data": {
    "entityById": {
      "mediaFileUrl": {
        "path": "/sites/default/files/media/icons/my-little-icon.svg"
      }
    }
  }
}
```
