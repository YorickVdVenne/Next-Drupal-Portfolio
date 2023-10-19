import type { Project } from './content-types/project/project'
import type { Button } from './generic'
import type { MainImage } from './media'
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
  profileImage: MainImage
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
  featuredProjects: Project[]
  projects: Project[]
}

export interface ContactSection {
  overlineTitle: string
  title: string
  bookmark: string
  description: string
  button: Button
}
