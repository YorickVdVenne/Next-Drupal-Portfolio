import React from 'react'
import styles from './styles.module.css'
import clsx from 'clsx'

import type { Job } from '@graphql/taxonomies/job/job'

import { Button } from '@components/atoms/Button/Component'

interface TabPanelProps {
  data: Job[]
  activeIndex: number
}

export default function TabPanel (props: TabPanelProps): JSX.Element {
  const { data, activeIndex } = props

  return (
    <>
      {data.map((content, key) => (
        <div key={key} className={clsx(styles.panel, { [styles.active]: activeIndex === key })}>
          <h4>
            <span>{content.role}</span>
            <span className={styles.company}>&nbsp;@&nbsp;<Button as='link' href={content.link} target='__blank'>{content.companyName}</Button></span>
          </h4>
          <p className={styles.range}>{content.period}</p>
          <ul>
            {content.jobDescription.map((description, key) => (
              <li key={key} dangerouslySetInnerHTML={{ __html: description.description }} />
            ))}
          </ul>
        </div>
      ))}
    </>
  )
}
