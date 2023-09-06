# Taxonomy

Adds a `children` field to the `TaxonomyTerm` interface.

## Extension

```graphql
extend interface TaxonomyTerm {
  children: [TaxonomyTerm]
}
```
