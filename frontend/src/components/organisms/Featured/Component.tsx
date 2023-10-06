import React from 'react'
import styles from './styles.module.css'
import Section from '@components/atoms/Section/Component'
import FeaturedListItem, { TextAlign } from '../../molecules/FeaturedListItem/Component'
import NumberedHeading from '@components/atoms/NumberedHeading/Component'
import sections from '@content/sections.json'

export default function Featured (): JSX.Element {
  const projects = sections.data.sections.projects

  return (
    <Section>
        <NumberedHeading id={projects.bookmark} number={3}>{projects.title}</NumberedHeading>
        <ul className={styles.featuredList}>
          {projects.projects.map((project, index) => {
            if (index % 2 === 0) {
              return <FeaturedListItem overlineText={projects.overlineText} item={project} textAlign={TextAlign.right} key={index} />
            } else return <FeaturedListItem overlineText={projects.overlineText} item={project} textAlign={TextAlign.left} key={index} />
          })}
        </ul>
    </Section>
  )
}
