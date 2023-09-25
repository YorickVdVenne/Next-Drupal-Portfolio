import React from 'react'
import styles from './styles.module.css'
import Section from '@components/atoms/Section/Component'
import FeaturedListItem, { TextAlign } from '../FeaturedListItem/Component'


export default function Featured (): JSX.Element {

  return (
    <Section>
        <h2 className={styles.numberedHeading}>Featured</h2>
        <ul className={styles.featuredList}>
            <FeaturedListItem textAlign={TextAlign.right} />
        </ul>
    </Section>
  )
}
