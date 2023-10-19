import type { MainImage } from '@graphql/media'
import type { Technologies } from '@graphql/taxonomies/technologies/technology'

export interface Project {
  id: string
  title: string
  summary: string
  technologies: Technologies[]
  githubLink?: string
  externalLink?: string
  mainImage: MainImage
  year: string
  madeAt?: string
  madeFor?: string
  description: string
  featured: boolean
}
