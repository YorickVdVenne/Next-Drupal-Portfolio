import React from 'react'
import styles from './styles.module.css'

interface SectionProps {
  children?: React.ReactNode
}

export default function Section (props: SectionProps): JSX.Element {
  const { children } = props

  return (
    <section className={styles.section}>
      {children}
    </section>
  )
}
