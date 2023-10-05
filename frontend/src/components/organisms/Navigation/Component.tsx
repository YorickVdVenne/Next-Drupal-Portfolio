import React from 'react'
import styles from './styles.module.css'
import { Button } from '@components/atoms/Button/Component'
import menu from '../../../../content/menu.json'
import { Logo } from '@components/atoms/Logo/Component'

export default function Navigation (): JSX.Element {

  console.log(menu.menu.actionButton)

  return (
    <nav className={styles.nav}>
      <div className={styles.innerNav}>
        <a href='/' className={styles.logo}>
          <Logo />
          <span className={styles.logoText}>Yorick</span>
        </a>
        <div className={styles.actions}>
          <ol>       
            {menu.menu.links.map((link, key) => (
              <li key={key}>
                <a href={link.url}>{link.label}</a>
              </li>
            ))}
          </ol>
          <Button 
            className={styles.button} 
            as='button' 
            onClick={() => window.location.href = menu.menu.actionButton.url}
          >
            {menu.menu.actionButton.label}
          </Button>
        </div>
      </div>
    </nav>
  )
}
