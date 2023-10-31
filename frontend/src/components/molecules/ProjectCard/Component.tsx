import React from 'react'
import styles from './styles.module.css'
import { hasValue } from '@misc/helpers'

import type { Technologies } from '@graphql/taxonomies/technologies/technology'
import type { ProjectDetail } from '@graphql/content-types/project/project'

import Card from '@components/atoms/Card/Component'
import { IconMapper } from '@components/atoms/Icons/Component'

interface ProjectCardProps {
  project: ProjectDetail
}

export default function ProjectCard (props: ProjectCardProps): JSX.Element {
  const { project } = props

  return (
    <Card className={styles.project}>
      <div className={styles.topWrapper}>
        <div className={styles.top}>
          <div className={styles.folder}>
            {IconMapper('folder')}
          </div>
          <div className={styles.links}>
            {hasValue(project.githubLink)
              ? (
                <a className={styles.link} href={project.githubLink} target='_blank' rel='noreferrer'>{IconMapper('github')}</a>
                )
              : null}
            {hasValue(project.externalLink)
              ? (
                <a className={styles.link} href={project.externalLink} target='_blank' rel='noreferrer'>{IconMapper('external-link')}</a>
                )
              : null}
          </div>
        </div>
        <h4 className={styles.title}>
          <a className={styles.titleLink} href={hasValue(project.externalLink) ? project.externalLink : '/'} target='_blank' rel='noreferrer'>{project.title}</a>
        </h4>
        <p className={styles.description}>{project.summary}</p>
      </div>
      <div className={styles.bottomWrapper}>
        <ul className={styles.techList}>
          {project.technologies.map((tech: Technologies, key: number) => (
            <li key={key}>{tech.name}</li>
          ))}
        </ul>
      </div>
    </Card>
  )
};
