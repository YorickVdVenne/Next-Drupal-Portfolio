import React from 'react'
import styles from './styles.module.css'
import gridStyles from '@components/atoms/Grid/styles.module.css'
import Section from '@components/atoms/Section/Component'

export default function About (): JSX.Element {

  return (
    <Section>
      <h2 className={styles.numberedHeading}>About me</h2>
      <div className={gridStyles.grid}>
        <div className={styles.content}>
          <p>Content</p>
          <p>Content</p>
          <p>Content</p>
          <p>Here are a few technologies I've been working with recently:</p>
          <ul className={styles.techList}>
            <li>TypeScript</li>
            <li>Next.js</li>
            <li>React</li>
            <li>JavaScript</li>
            <li>Node.js</li>
            <li>Drupal</li>
          </ul>
        </div>
        <div className={styles.profileImage}>
          Image
        </div>  
      </div>
    </Section>
  )
}
