import React from 'react'
import styles from './styles.module.css'
import Link from 'next/link'
import { hasValue } from '@misc/helpers'
import clsx from 'clsx'

import type { ActionButton, MenuItem } from '@graphql/menus'

import { Button } from '@components/atoms/Button/Component'

interface NavigationItemsProps {
  links?: MenuItem[]
  actionButton?: ActionButton
  desktop?: boolean
  setMenuOpen?: (menuOpen: boolean) => void
}

export default function NavigationItems (props: NavigationItemsProps): JSX.Element {
  const { links, actionButton, desktop, setMenuOpen } = props

  return (
    <div className={clsx(styles.navItems, { [styles.desktop]: desktop })}>
      <ol className={clsx({ [styles.desktop]: desktop })}>
        {hasValue(links)
          ? links.map((link, key) => (
            <li key={key} className={clsx({ [styles.desktop]: desktop })}>
              <Link
                href={link.url}
                className={clsx({ [styles.desktop]: desktop })}
                onClick={(e) => {
                  const sectionId = link.url.replace('/#', '')
                  const section = document.getElementById(sectionId)
                  if (section != null) {
                    e.preventDefault()
                    window.scrollTo({
                      top: section.offsetTop - 100,
                      behavior: 'smooth'
                    })
                  }
                  if (hasValue(setMenuOpen)) setMenuOpen(false)
                }}
              >
                {link.label}
              </Link>
            </li>
          ))
          : ''}
      </ol>
      {hasValue(actionButton)
        ? (
          <Link href={actionButton.url} target='_blank'>
            <Button
              className={clsx(styles.button, { [styles.desktop]: desktop })}
              as='button'
              size={!hasValue(desktop) ? 'large' : undefined}
            >
              {actionButton.label}
            </Button>
          </Link>
          )
        : ''}
    </div>
  )
};
