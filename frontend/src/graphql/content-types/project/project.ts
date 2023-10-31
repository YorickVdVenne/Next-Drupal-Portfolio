import type { MediaImage } from '@graphql/media'
import type { Technologies } from '@graphql/taxonomies/technologies/technology'

export interface Project {
  id: string
  title: string
  summary: string
  technologies: Technologies[]
  githubLink?: string
  externalLink?: string
  mainImage: MediaImage
  screenshots?: MediaImage[]
  year: string
  madeAt?: string
  madeFor?: string
  description: string
  nextProjectId?: string
  featured: boolean
}
