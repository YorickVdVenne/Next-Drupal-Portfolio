import React from 'react'
import styles from './styles.module.css'
import Section from '@components/atoms/Section/Component'
import FeaturedListItem, { TextAlign } from '../FeaturedListItem/Component'
import NumberedHeading from '@components/atoms/NumberedHeading/Component'
import sections from '@content/sections.json'

export default function Featured (): JSX.Element {
  const featured = sections.data.sections.featured

  return (
    <Section>
        <NumberedHeading id={featured.bookmark} number={3}>{featured.title}</NumberedHeading>
        <ul className={styles.featuredList}>
          {featured.projects.map((project, index) => {
            if (index % 2 === 0) {
              return <FeaturedListItem overlineText={featured.overlineText} item={project} textAlign={TextAlign.right} key={index} />
            } else return <FeaturedListItem overlineText={featured.overlineText} item={project} textAlign={TextAlign.left} key={index} />
          })}
        </ul>
    </Section>
  )
}
