import React from 'react'
import styles from './styles.module.css'
import clsx from 'clsx'

export enum Allign {
  center = 'center',
  left = 'left'
}

interface SectionProps {
  children?: React.ReactNode
  allign?: Allign
  maxWidth?: number
  fullHeight?: boolean
}

export default function Section (props: SectionProps): JSX.Element {
  const { children, allign, maxWidth, fullHeight } = props

  return (
    <section
      className={clsx(styles.section, {
        [styles.allignCenter]: allign === Allign.center,
        [styles.fullHeight]: fullHeight
      })}
      style={{ maxWidth: maxWidth ?? 1000 }}
    >
      {children}
    </section>
  )
}
