import React from 'react'
import styles from './styles.module.css'
import Section, { Allign } from '@components/atoms/Section/Component'
import { Button } from '@components/atoms/Button/Component'
import ProjectCardCollection from '../ProjectCardCollection/Component'

export default function Projects (): JSX.Element {

  return (
    <Section allign={Allign.center}>
      <h3 className={styles.title}>Other Noteworthy Projects</h3>
      <Button as='link' href='/projects' className={styles.link}>view the archive</Button>
      <ProjectCardCollection />
      <Button as='button' size='large' className={styles.button} onClick={() => console.log('show more')}>Show More</Button>
    </Section>
  )
}
