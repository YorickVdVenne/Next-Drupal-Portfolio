import { MetatagsFragment } from '@graphql/metatags'
import { AboutSection, HeaderSection, ContactSection, ExperienceSection, ProjectSection } from '@graphql/sections'

export interface HomeData {
  sections: {
    header: HeaderSection
    about: AboutSection
    experience: ExperienceSection
    projects: ProjectSection
    contact: ContactSection
  }
  metatags: MetatagsFragment
}
