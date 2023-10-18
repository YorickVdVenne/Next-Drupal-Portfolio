import React from 'react'
import styles from './styles.module.css'
import clsx from 'clsx'

interface MainContainerProps {
  children?: React.ReactNode
  paddingBlock?: boolean
  maxWidth?: number
}

export default function MainContainer (props: MainContainerProps): JSX.Element {
  const { children, paddingBlock, maxWidth } = props

  return (
    <main
      className={clsx(styles.mainContainer, { [styles.paddingBlock]: paddingBlock })}
      style={{ maxWidth: maxWidth ?? 1600 }}
    >
      {children}
    </main>
  )
}
