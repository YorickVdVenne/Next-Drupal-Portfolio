import React from 'react'
import styles from './styles.module.css'
import clsx from 'clsx'

interface ButtonProps {
  children: React.ReactNode
  variant?: 'primary' | 'secondary'
}

type Props =
| ({ as: 'link' } & ButtonProps & React.AnchorHTMLAttributes<HTMLAnchorElement>)
| ({ as: 'button' } & ButtonProps & React.ButtonHTMLAttributes<HTMLButtonElement>)

export function Button (props: Props): JSX.Element {
  const { children, variant, ...componentProps } = props

  if (componentProps.as === 'link') {
    const classes = clsx(componentProps.className, styles.link, variant === 'secondary' ? styles.linkSecondary : styles.linkPrimary)
    const { as, className, ...linkProps } = componentProps
    return <a className={classes} {...linkProps}>{children}</a>
  } else {
    const classes = clsx(componentProps.className, styles.button)
    const { as, className, ...buttonProps } = componentProps
    return <button className={classes} {...buttonProps}>{children}</button>
  }
}
