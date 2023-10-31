import React from 'react'
import styles from './styles.module.css'
import clsx from 'clsx'

interface MainContainerProps {
  children?: React.ReactNode
  paddingBlock?: boolean
  paddingBlockStart?: boolean
  maxWidth?: number
}

export default function MainContainer (props: MainContainerProps): JSX.Element {
  const { children, paddingBlock, paddingBlockStart, maxWidth } = props

  return (
    <main
      className={clsx(styles.mainContainer, { 
        [styles.paddingBlock]: paddingBlock,
        [styles.paddingBlockStart]: paddingBlockStart 
      })}
      style={{ maxWidth: maxWidth ?? 1600 }}
    >
      {children}
    </main>
  )
}
