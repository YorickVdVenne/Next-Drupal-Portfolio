import React from 'react'
import styles from './styles.module.css'
import { Button } from '@components/atoms/Button/Component'
import * as Icons from '@components/atoms/Icons/Component'

export default function Footer (): JSX.Element {

  return (
    <footer className={styles.footer}>
      <div className={styles.credits}>
        <Button as='link' className={styles.link} variant='secondary' href='https://github.com/YorickVdVenne/Nextjs-Drupal-Portfolio' target='_blank'>
            Built by Yorick Van de Venne
            <span className={styles.icon}>{Icons.IconMapper('github')}</span>
        </Button>
      </div>
    </footer>
  )
}
