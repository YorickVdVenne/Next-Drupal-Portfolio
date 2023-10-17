import React from 'react'
import styles from './styles.module.css'
import Section, { Allign } from '@components/atoms/Section/Component'
import { Button } from '@components/atoms/Button/Component'
import ProjectCardCollection from '../ProjectCardCollection/Component'
import { Project } from '@graphql/content-types/project/project'

interface ProjectsProps {
  projectData: Project[]
}

export default function Projects (props: ProjectsProps): JSX.Element {

  return (
    <Section allign={Allign.center}>
      <h3 className={styles.title}>Other Noteworthy Projects</h3>
      <Button as='link' href='/archive' className={styles.link}>view the archive</Button>
      <ProjectCardCollection projects={props.projectData} />
      {/* <Button as='button' size='large' className={styles.button} onClick={() => console.log('show more')}>Show More</Button> */}
    </Section>
  )
}
