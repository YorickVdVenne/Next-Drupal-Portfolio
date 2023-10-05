import React from 'react'
import styles from './styles.module.css'
import { Button } from '@components/atoms/Button/Component'
import clsx from 'clsx'

interface TabPanelProps {
  data: Array<{
    name: string,
    role: string,
    period: string,
    responsibilities: Array<{
      description: string
    }>
  }>
  activeIndex: number
}

export default function TabPanel (props: TabPanelProps): JSX.Element {
  const { data, activeIndex } = props


  return (
    <>
      {data.map((content, key) => (
        <div key={key} className={clsx(styles.panel, {[styles.active]: activeIndex === key})}>
          <h4>
            <span>{content.role}</span>
            <span className={styles.company}>&nbsp;@&nbsp;<Button as='link' href="" target='__blank'>{content.name}</Button></span>
          </h4>
          <p className={styles.range}>{content.period}</p>
          <ul>
            {content.responsibilities.map((responsibility, key) => (
              <li key={key}>{responsibility.description}</li>
            ))}
          </ul>
        </div>
      ))}    
    </>
  )
}
