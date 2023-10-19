import React from 'react'
import styles from './styles.module.css'
import Image from 'next/image'

import type { AboutSection } from '@graphql/sections'

import gridStyles from '@components/atoms/Grid/styles.module.css'
import Section from '@components/atoms/Section/Component'
import NumberedHeading from '@components/atoms/NumberedHeading/Component'

interface AboutProps {
  aboutData: AboutSection
}

export default function About (props: AboutProps): JSX.Element {
  return (
    <Section maxWidth={900}>
      <NumberedHeading number={1} id={props.aboutData.bookmark}>{props.aboutData.title}</NumberedHeading>
      <div className={gridStyles.grid}>
        <div className={styles.content}>
          <div dangerouslySetInnerHTML={{ __html: props.aboutData.description }} />
          <ul className={styles.techList}>
            {props.aboutData.technologies.map((tech, key) => (
              <li key={key}>{tech.name}</li>
            ))}
          </ul>
        </div>
        <div className={styles.profileImage}>
          <div className={styles.imageWrapper}>
            <Image
              src={props.aboutData.profileImage.url}
              alt={props.aboutData.profileImage.alt}
              className={styles.image}
              quality={100}
              width={1000}
              height={1000}
            />
          </div>
        </div>
      </div>
    </Section>
  )
}
