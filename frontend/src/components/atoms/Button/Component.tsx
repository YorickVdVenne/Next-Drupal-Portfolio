import React from 'react'
import styles from './styles.module.css'
import clsx from 'clsx'
import InternalOrExternalLink from '@lib/link/Component'

interface ButtonProps {
  children: React.ReactNode
  variant?: 'primary' | 'secondary'
  size?: 'large'
}

type Props =
| ({ as: 'link' } & ButtonProps & React.AnchorHTMLAttributes<HTMLAnchorElement>)
| ({ as: 'button' } & ButtonProps & React.ButtonHTMLAttributes<HTMLButtonElement>)

export function Button (props: Props): JSX.Element {
  const { children, variant, size, ...componentProps } = props

  if (componentProps.as === 'link') {
    const classes = clsx(componentProps.className, styles.link, variant === 'secondary' ? styles.linkSecondary : styles.linkPrimary)
    const { as, className, ...linkProps } = componentProps
    return <InternalOrExternalLink className={classes} {...linkProps}>{children}</InternalOrExternalLink>
  } else {
    const classes = clsx(componentProps.className, styles.button, {[styles.largeButton]: size === 'large'})
    const { as, className, ...buttonProps } = componentProps
    return <button className={classes} {...buttonProps}>{children}</button>
  }
}
