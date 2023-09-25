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
}

export default function Section (props: SectionProps): JSX.Element {
  const { children, allign, maxWidth } = props

  return (
    <section 
      className={clsx(styles.section, {
        [styles.allignCenter]: allign === Allign.center
      })}
      style={{ maxWidth: maxWidth ? maxWidth : 1000}}
    >
      {children}
    </section>
  )
}
