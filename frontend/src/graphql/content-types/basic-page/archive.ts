import type { MetatagsFragment } from '@graphql/metatags'
import type { ProjectDetail } from '../project/project'

export interface ArchiveData {
  title: string
  shortText: string
  projects: ProjectDetail[]
  metatags: MetatagsFragment
}
