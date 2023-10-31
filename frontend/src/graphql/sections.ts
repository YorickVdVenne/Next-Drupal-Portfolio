import type { ProjectDetail } from './content-types/project/project'
import type { Button } from './generic'
import type { MediaImage } from './media'
import type { Company } from './taxonomies/company/company'
import type { Job } from './taxonomies/job/job'
import type { Technologies } from './taxonomies/technologies/technology'

export interface HeaderSection {
  introText: string
  name: string
  punchline: string
  shortDescription: string
  button: Button
}

export interface AboutSection {
  title: string
  bookmark: string
  description: string
  technologies: Technologies[]
  profileImage: MediaImage
}

export interface ExperienceSection {
  title: string
  bookmark: string
  companies: Company[]
  jobs: Job[]
}

export interface ProjectSection {
  title: string
  bookmark: string
  featuredProjects: ProjectDetail[]
  projects: ProjectDetail[]
}

export interface ContactSection {
  overlineTitle: string
  title: string
  bookmark: string
  description: string
  button: Button
}
