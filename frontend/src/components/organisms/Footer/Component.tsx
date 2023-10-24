import React from 'react'
import styles from './styles.module.css'
import clsx from 'clsx'
import { hasValue } from '@misc/helpers'

import type { FooterData } from '@graphql/menus'

import { Button } from '@components/atoms/Button/Component'
import * as Icons from '@components/atoms/Icons/Component'
import { IconMapper } from '@components/atoms/Icons/Component'

interface FooterProps {
  footer?: FooterData
}

export default function Footer (props: FooterProps): JSX.Element {
  const { footer } = props

  return (
    <footer className={styles.footer}>
      <div className={styles.socials}>
        <ul>
          {hasValue(footer) 
            ? footer.socials.map((social, key) => (
              <li key={key}>
                <a
                  className={styles.icon}
                  href={social.url}
                  target='_blank'
                  title={social.label}
                  rel='noreferrer'
                >
                  {IconMapper(social.icon)}
                </a>
              </li>
            ))
            : ''}
        </ul>
      </div>
      <div className={styles.credits}>
        {hasValue(footer) 
        ? <Button as='link' className={styles.link} variant='secondary' href={footer.actionButton.url} target='_blank'>
            {footer.actionButton.label}
            {hasValue(footer.actionButton.icon)
              ? (
                <span className={clsx(styles.icon, styles.hideOnMobile)}>{Icons.IconMapper(footer.actionButton.icon)}</span>
                )
              : null}
          </Button>
        : ''}
        
      </div>
    </footer>
  )
}
