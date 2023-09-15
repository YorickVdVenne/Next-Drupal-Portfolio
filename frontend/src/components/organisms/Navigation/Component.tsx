import React from 'react'
import styles from './styles.module.css'
import Logo from '@components/atoms/Logo/Component'
import { Button } from '@components/atoms/Button/Component'

export default function Navigation (): JSX.Element {

  return (
    <nav className={styles.nav}>
      <div className={styles.innerNav}>
        <Logo />
        <div className={styles.actions}>
          <ol>
            <li>
              <a>About</a>
            </li>
            <li>
              <a>Experience</a>
            </li>
            <li>
              <a>Projects</a>
            </li>
            <li>
              <a>Contact</a>
            </li>
          </ol>
          <Button className={styles.button} as='button'>Resume</Button>
        </div>
      </div>
    </nav>
  )
}
