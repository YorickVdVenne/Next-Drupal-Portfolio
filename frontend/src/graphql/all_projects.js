import { gql } from '@apollo/client'

export const ALL_PROJECTS_QUERY = gql`
query {
    projects(limit: 10, offset: 0) {
        total

        items {
            title
            brand
            description
            period
            mainImage
            roles {
                name
            }
            technologies {
                name
            }
        }
    }
}
`