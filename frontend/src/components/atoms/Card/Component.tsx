import React from 'react'
import styles from './styles.module.css'
import clsx from 'clsx'

interface CardProps {
  children?: React.ReactNode
}

export default function Card (props: CardProps): JSX.Element {
  const { children } = props

  return (
    <div className={clsx(styles.card)}>
      {children}
    </div>
  )
}
