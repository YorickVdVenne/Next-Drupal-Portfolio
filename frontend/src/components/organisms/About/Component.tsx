import React from 'react'
import styles from './styles.module.css'
import gridStyles from '@components/atoms/Grid/styles.module.css'
import Section from '@components/atoms/Section/Component'
import Image from 'next/image'
import profileImage from '@public/images/profile-image.png'
import NumberedHeading from '@components/atoms/NumberedHeading/Component'
import sections from '@content/sections.json'

export default function About (): JSX.Element {

  const about = sections.data.sections.about

  return (
    <Section maxWidth={900}>
      <NumberedHeading number={1} id={about.bookmark} >{about.title}</NumberedHeading>
      <div className={gridStyles.grid}>
        <div className={styles.content}>
          <p>{about.description}</p>
          <p>{about.techDescription}</p>
          <ul className={styles.techList}>
            {about.technologies.map((tech, key) => (
              <li key={key}>{tech.name}</li>
            ))}
          </ul>
        </div>
        <div className={styles.profileImage}>
          <div className={styles.imageWrapper}>
            <Image 
              src={profileImage}
              alt={about.profileImage.alt}
              className={styles.image}
              quality={100}
            />
          </div>
        </div>  
      </div>
    </Section>
  )
}
