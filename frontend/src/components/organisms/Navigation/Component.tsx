import React, { useEffect, useState } from 'react'
import styles from './styles.module.css'
import { Button } from '@components/atoms/Button/Component'
import menu from '@content/menu.json'
import { Logo } from '@components/atoms/Logo/Component'
import clsx from 'clsx'

export default function Navigation (): JSX.Element {
  const [scrolled, setScrolled] = useState(false);
  const [scrollTop, setScrollTop] = useState(true)

  useEffect(() => {
    let lastScrollY= 0

    const handleScroll = () => {
      const currentScrollY = window.scrollY
      
      if (currentScrollY !== 0) {
        setScrollTop(false) 
      } else setScrollTop(true)

      if (window.scrollY > lastScrollY) {
          setScrolled(true)
      } else setScrolled(false)

      lastScrollY = currentScrollY
    };

    window.addEventListener('scroll', handleScroll)

    return () => {
        window.removeEventListener('scroll', handleScroll)
    }
}, [])

  return (
    <nav id="navbar" className={clsx(styles.nav, {
      [styles.hidden] : scrolled,
      [styles.visible]: !scrolled,
      [styles.small]: !scrolled && !scrollTop
    })}
    >
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
