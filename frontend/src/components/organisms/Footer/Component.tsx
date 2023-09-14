import React from 'react'
import styles from './styles.module.css'
import { Button } from '@components/atoms/Button/Component'
import * as Icons from '@components/atoms/Icons/Component'

export default function Footer (): JSX.Element {

  return (
    <footer className={styles.footer}>
      <div className={styles.credits}>
        <Button as='link'>
          Built by Yorick Van de Venne
          {Icons.IconMapper('github')}
        </Button>
      </div>
    </footer>
  )
}
