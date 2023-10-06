import React from 'react'
import styles from './styles.module.css'
import clsx from 'clsx'

interface CardProps {
  children?: React.ReactNode
  className?: string
}

export default function Card (props: CardProps): JSX.Element {
  const { children, className } = props

  return (
    <div className={clsx(styles.card, className ? className : '')}>
      {children}
    </div>
  )
}
