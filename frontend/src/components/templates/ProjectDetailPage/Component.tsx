import React from 'react'
import styles from './styles.module.css'

import type{ Project } from '@graphql/content-types/project/project'
import Image from 'next/image'
import MainContainer from '@components/atoms/MainContainer/Component'

interface ProjectDetailPageProps {
  project: Project
}

export default function ProjectDetailPage (props: ProjectDetailPageProps): JSX.Element {
  const { project } = props

  return (
    <MainContainer maxWidth={1200}>
      
      <div className={styles.mainImageWrapper}>
        <Image className={styles.mainImage} src={project.mainImage.url} alt={project.mainImage.alt} width={1000} height={1000}/>
      </div>
      <h1 className={styles.projectTitle}>{project.title}</h1>
      <hr className={styles.separator} />
    </MainContainer>
  )
};
