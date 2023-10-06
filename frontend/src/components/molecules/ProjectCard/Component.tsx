import React from 'react'
import styles from './styles.module.css'
import { Project } from '@components/molecules/FeaturedListItem/Component'
import Card from '@components/atoms/Card/Component'
import { IconMapper } from '@components/atoms/Icons/Component'

interface ProjectCardProps {
  project: Project
}

export default function ProjectCard (props: ProjectCardProps): JSX.Element {
     const { project } = props
  
  return (
    <div className={styles.project}>
      <div>
        <div className={styles.top}>
          <div className={styles.folder}>
            {IconMapper('folder')}
          </div>
          <div className={styles.links}>
            {project.codeLink ? (
              <a className={styles.link} href={project.codeLink} target='_blank'>{IconMapper('github')}</a>
            ): ''}
            {project.siteLink ? (
              <a className={styles.link} href={project.siteLink} target='_blank'>{IconMapper('external-link')}</a>
            ): ''}
          </div>
        </div>
        <h4 className={styles.title}>
          <a className={styles.titleLink} href={project.siteLink ? project.siteLink : '/'} target='_blank'>{project.title}</a>
        </h4>
        <p className={styles.description}>{project.summary}</p>
      </div>
      <div>
        <ul className={styles.techList}>
          {project.technologies.map((tech, key) => (
            <li key={key}>{tech.name}</li>
          ))}
        </ul>
      </div>
    </div>
  );
};
