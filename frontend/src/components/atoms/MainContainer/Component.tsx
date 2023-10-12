import React from 'react'
import styles from './styles.module.css'
import clsx from 'clsx'

interface MainContainerProps {
  children?: React.ReactNode
  paddingBlock?: boolean
}

export default function MainContainer (props: MainContainerProps): JSX.Element {
  const { children, paddingBlock } = props

  return (
    <main className={clsx(styles.mainContainer, {[styles.paddingBlock]: paddingBlock})}>
      {children}
    </main>
  )
}
