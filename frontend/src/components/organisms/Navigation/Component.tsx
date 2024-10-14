import React, { useEffect, useState } from 'react'
import styles from './styles.module.css'
import clsx from 'clsx'
import Link from 'next/link'
import { useRouter } from 'next/router'
import { useTranslation } from 'next-i18next'

import type { MainMenu } from '@graphql/menus'

import { Logo } from '@components/atoms/Logo/Component'
import NavigationMenu from '@components/molecules/NavigationMenu/Component'
import NavigationItems from '@components/molecules/NavigationItems/Component'

interface NavigationProps {
  mainMenu?: MainMenu
}

export default function Navigation (props: NavigationProps): JSX.Element {
  const router = useRouter()
  const { t } = useTranslation('menu')
  const [scrolled, setScrolled] = useState(false)
  const [scrollTop, setScrollTop] = useState(true)

  useEffect(() => {
    let lastScrollY = 0

    const handleScroll = (): void => {
      const currentScrollY = window.scrollY

      setScrollTop(currentScrollY === 0)
      setScrolled(currentScrollY > lastScrollY && currentScrollY > 0)

      lastScrollY = currentScrollY
    }

    window.addEventListener('scroll', handleScroll)
    return () => {
      window.removeEventListener('scroll', handleScroll)
    }
  }, [])

  return (
    <nav
      id='navbar' className={clsx(styles.nav, {
        [styles.hidden]: scrolled,
        [styles.visible]: !scrolled,
        [styles.small]: !scrolled && !scrollTop
      })}
    >
      <div className={styles.innerNav}>
        <Link
          href='/' className={styles.logo} onClick={(e) => {
            if (router.pathname === '/') {
              e.preventDefault()
              window.scrollTo({ top: 0, behavior: 'smooth' })
            }
          }}
        >
          <Logo />
          <span className={styles.logoText}>{t('logoText')}</span>
        </Link>
        <NavigationItems links={props.mainMenu?.links} actionButton={props.mainMenu?.actionButton} desktop />
        <NavigationMenu menu={props.mainMenu} />
      </div>
    </nav>
  )
}
