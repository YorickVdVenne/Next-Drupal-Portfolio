import React from 'react'
import styles from './styles.module.css'
import { Button } from '@components/atoms/Button/Component'
import gridStyles from '@components/atoms/Grid/styles.module.css'
import clsx from 'clsx'

export default function Header (): JSX.Element {

  return (
    <header className={styles.header}>
      <h1 className={styles.h1}>Hi, my name is</h1>
      <h2 className={styles.h2}>Yorick Van de Venne.</h2>
      <h3>I build things for the web.</h3>
      <div className={clsx(gridStyles.grid, styles.wrapper)}>
        <p className={styles.text}>
          Some nice text about me, even more. Some nice text about me, even more. Some nice text about me, even more. Some nice text about me, even more.
          Some nice text about me, even more. Some nice text about me, even more. Some nice text about me, even more. <Button as='link' onClick={() => console.log('test')}>Link</Button>
        </p>
        <Button onClick={() => window.location.href = '/resume.pdf'} as='button' size='large' className={styles.button}>Resume</Button>
      </div>
    </header>
  )
}
