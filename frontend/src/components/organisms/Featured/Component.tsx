import React from 'react'
import styles from './styles.module.css'

import type { ProjectSection } from '@graphql/sections'

import Section from '@components/atoms/Section/Component'
import FeaturedListItem, { TextAlign } from '../../molecules/FeaturedListItem/Component'
import NumberedHeading from '@components/atoms/NumberedHeading/Component'

interface FeaturedProps {
  featuredData: ProjectSection
}

export default function Featured (props: FeaturedProps): JSX.Element {
  return (
    <Section>
      <NumberedHeading id={props.featuredData.bookmark} number={3}>{props.featuredData.title}</NumberedHeading>
      <ul className={styles.featuredList}>
        {props.featuredData.featuredProjects.map((project, index) => {
          if (index % 2 === 0) {
            return <FeaturedListItem item={project} textAlign={TextAlign.right} key={index} />
          } else return <FeaturedListItem item={project} textAlign={TextAlign.left} key={index} />
        })}
      </ul>
    </Section>
  )
}
