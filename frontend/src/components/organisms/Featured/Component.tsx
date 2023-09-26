import React from 'react'
import styles from './styles.module.css'
import Section from '@components/atoms/Section/Component'
import FeaturedListItem, { TextAlign } from '../FeaturedListItem/Component'
import projects from '../../../../content/projects.json'

export default function Featured (): JSX.Element {

  return (
    <Section>
        <h2 className={styles.numberedHeading}>Featured</h2>
        <ul className={styles.featuredList}>
          {projects.data.projects.items.map((item, index) => {
            if (index % 2 === 0) {
              return <FeaturedListItem item={item} textAlign={TextAlign.right} key={index} />
            } else return <FeaturedListItem item={item} textAlign={TextAlign.left} key={index} />
          })}
        </ul>
    </Section>
  )
}
