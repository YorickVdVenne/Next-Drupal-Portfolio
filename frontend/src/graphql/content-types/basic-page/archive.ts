import type { MetatagsFragment } from '@graphql/metatags'
import type { Project } from '../project/project'

export interface ArchiveData {
  title: string
  shortText: string
  projects: Project[]
  metatags: MetatagsFragment
}
