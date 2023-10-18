import React from 'react'
import styles from './styles.module.css'
import { Button } from '@components/atoms/Button/Component'
import * as Icons from '@components/atoms/Icons/Component'
import { IconMapper } from '@components/atoms/Icons/Component'
import { FooterData } from '@graphql/menus'

interface FooterProps {
  footer: FooterData
}

export default function Footer (props: FooterProps): JSX.Element {
  const { footer } = props

  return (
    <footer className={styles.footer}>
      <div className={styles.socials}>
        <ul>
          {footer.socials.map((social, key) => (
            <li key={key}>
              <a href={social.url} target='_blank' title={social.label} rel='noreferrer'>{IconMapper(social.icon)}</a>
            </li>
          ))}
        </ul>
      </div>
      <div className={styles.credits}>
        <Button as='link' className={styles.link} variant='secondary' href={footer.actionButton.url} target='_blank'>
          {footer.actionButton.label}
          {footer.actionButton.icon
            ? (
              <span className={styles.icon}>{Icons.IconMapper(footer.actionButton.icon)}</span>
              )
            : ''}
        </Button>
      </div>
    </footer>
  )
}
