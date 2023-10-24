import React, { useEffect, useRef, useState } from 'react'
import styles from './styles.module.css'
import { Helmet } from 'react-helmet'
import { useOnClickOutside } from '@misc/useOnClickOutside'
import clsx from 'clsx'

import type { MainMenu } from '@graphql/menus'

import NavigationItems from '../NavigationItems/Component'

interface NavigationMenuProps {
  menu?: MainMenu
  show?: boolean
}

export default function NavigationMenu (props: NavigationMenuProps): JSX.Element {
  const [menuOpen, setMenuOpen] = useState(false)

  const hamBoxStyles: Record<string, string> = {
    '--transition-delay': `${menuOpen ? '0.12s' : '0s'}`,
    '--transform-rotation': `${menuOpen ? '225deg' : '0deg'}`,
    '--transition-timing-function': `${menuOpen ? '0.215, 0.61, 0.355, 1' : '0.55, 0.055, 0.675, 0.19'}`,

    '--inner-before-width': `${menuOpen ? '100%' : '120%'}`,
    '--inner-before-top': `${menuOpen ? '0' : '-10px'}`,
    '--inner-before-opacity': `${menuOpen ? '0' : '1'}`,
    '--inner-before-transition': `${menuOpen ? 'var(--ham-before-active' : 'var(--ham-before)'}`,

    '--inner-after-width': `${menuOpen ? '100%' : '80%'}`,
    '--inner-after-bottom': `${menuOpen ? '0' : '-10px'}`,
    '--inner-after-transform': `${menuOpen ? '-90deg' : '0'}`,
    '--inner-after-transition': `${menuOpen ? 'var(--ham-after-active)' : 'var(--ham-after)'}`
  }

  const asideStyles: Record<string, string> = {
    '--aside-transform': `${menuOpen ? '0' : '100'}vw`,
    '--aside-visibility': `${menuOpen ? 'visible' : 'hidden'}`
  }

  const onKeyDown = (e: { key: any }): void => {
    if (e.key === 'Escape' || e.key === 'Esc') {
      setMenuOpen(false)
    }
  }

  const onResize = (): void => {
    if (window.innerWidth > 768) {
      setMenuOpen(false)
    }
  }

  useEffect(() => {
    document.addEventListener('keydown', onKeyDown)
    window.addEventListener('resize', onResize)

    return () => {
      document.removeEventListener('keydown', onKeyDown)
      window.removeEventListener('resize', onResize)
    }
  }, [])

  const wrapperRef = useRef(null)
  useOnClickOutside(wrapperRef, () => {
    setMenuOpen(false)
  })

  return (
    <div className={clsx(styles.menu, {[styles.show]: props.show})}>
      <Helmet>
        <html className={menuOpen ? 'blur' : ''} />
      </Helmet>

      <div ref={wrapperRef}>
        <button onClick={() => { setMenuOpen(!menuOpen) }} className={styles.hamburgerButton}>
          <div className={styles.hamBox}>
            <div className={styles.hamBoxInner} style={hamBoxStyles} />
          </div>
        </button>
        <aside className={clsx(styles.aside, {[styles.show]: props.show})} aria-hidden={!menuOpen} tabIndex={menuOpen ? 1 : -1} style={asideStyles}>
          <NavigationItems links={props.menu?.links} actionButton={props.menu?.actionButton} setMenuOpen={setMenuOpen} />
        </aside>
      </div>
    </div>
  )
};
