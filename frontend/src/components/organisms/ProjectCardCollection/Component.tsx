import React from 'react'
import styles from './styles.module.css'
import ProjectCard from '@components/molecules/ProjectCard/Component'
import { Project } from '@graphql/content-types/project/project'

interface ProjectCardCollectionProps {
  projects: Project[]
}

export default function ProjectCardCollection (props: ProjectCardCollectionProps): JSX.Element {
  const { projects } = props

  return (
    <ul className={styles.projectCollection}>
      {projects.map((item, key) => (
        <li key={key} className={styles.item}>
          <ProjectCard project={item} />
        </li>
      ))}
    </ul>
  )
};
