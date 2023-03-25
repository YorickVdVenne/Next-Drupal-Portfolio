import clsx from 'clsx'
import React from 'react'
import styles from './styles.module.css'

interface ContainerProps {
  children?: React.ReactNode
}

export default function Container (props: ContainerProps): JSX.Element {
  const { children } = props

  return (
    <div className={styles.container}>
      {children}
    </div>
  )
}
