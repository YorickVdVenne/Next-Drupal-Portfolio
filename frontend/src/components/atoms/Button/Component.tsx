import React from 'react'
import styles from './styles.module.css'

interface ButtonProps {
  children: React.ReactNode
}

type Props =
| ({ as: 'link' } & ButtonProps & React.AnchorHTMLAttributes<HTMLAnchorElement>)
| ({ as: 'button' } & ButtonProps & React.ButtonHTMLAttributes<HTMLButtonElement>)

export function Button (props: Props): JSX.Element {
  const { children, ...componentProps } = props

  if (componentProps.as === 'link') {
    return <a className={styles.link}>{children}</a>
  } else return <button className={styles.button}>{children}</button>
}
