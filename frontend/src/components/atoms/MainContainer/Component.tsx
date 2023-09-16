import React from 'react'
import styles from './styles.module.css'

interface MainContainerProps {
  children?: React.ReactNode
}

export default function MainContainer (props: MainContainerProps): JSX.Element {
  const { children } = props

  return (
    <main className={styles.mainContainer}>
      {children}
    </main>
  )
}
