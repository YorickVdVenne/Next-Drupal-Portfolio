import React from 'react'
import styles from './styles.module.css'
import clsx from 'clsx'
import { hasValue } from '@misc/helpers'

interface NumberedHeadingProps {
  children?: React.ReactNode
  id?: string
  number?: number
  mono?: boolean
}

export default function NumberedHeading (props: NumberedHeadingProps): JSX.Element {
  const { children, id, number, mono } = props

  return (
    <h2
      id={id}
      className={clsx(styles.numberedHeading, {
        [styles.mono]: mono,
        [styles.hideNumber]: !hasValue(number)
      })}
      style={{ counterIncrement: `section ${hasValue(number) ? number : ''}` }}
    >
      {children}
    </h2>
  )
}
