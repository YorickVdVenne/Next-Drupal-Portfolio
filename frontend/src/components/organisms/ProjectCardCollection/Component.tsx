import React from 'react'
import styles from './styles.module.css'
import gridStyles from '@components/atoms/Grid/styles.module.css'
import ProjectCard from '@components/molecules/ProjectCard/Component';
import projects from '@content/projects.json'

interface ProjectCardCollectionProps {
  // prop: string
}

export default function ProjectCardCollection (props: ProjectCardCollectionProps): JSX.Element {
    //  const { prop } = props
  
  return (
    <ul className={styles.projectCollection}>
      {projects.data.projects.items.map((item, key) => (
        <li key={key} className={styles.project}>
          <ProjectCard project={item}/>
        </li>
      ))}
    </ul>
  );
};
