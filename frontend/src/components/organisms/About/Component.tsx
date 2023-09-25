import React from 'react'
import styles from './styles.module.css'
import gridStyles from '@components/atoms/Grid/styles.module.css'
import Section from '@components/atoms/Section/Component'
import Image from 'next/image'
import profileImage from '../../../../public/images/profile-image.png'

export default function About (): JSX.Element {

  return (
    <Section maxWidth={900}>
      <h2 className={styles.numberedHeading}>About me</h2>
      <div className={gridStyles.grid}>
        <div className={styles.content}>
          <p>Hello! My name is Yorick and I enjoy creating things that live on the internet. My interest in web development started back in 2012 when I decided to try editing custom Tumblr themes — turns out hacking together a custom reblog button taught me a lot about HTML & CSS!</p>
          <p>Hello! My name is Yorick and I enjoy creating things that live on the internet. My interest in web development started back in 2012 when I decided to try editing custom Tumblr themes — turns out hacking together a custom reblog button taught me a lot about HTML & CSS!</p>
          <p>I also recently launched a course that covers everything you need to build a web app with the Spotify API using Node & React.</p>
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
          <div className={styles.imageWrapper}>
            <Image 
              src={profileImage}
              alt='Picture of Yorick'
              className={styles.image}
            />
          </div>
        </div>  
      </div>
    </Section>
  )
}
