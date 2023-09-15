import React from 'react'
import dynamic from 'next/dynamic'
import styles from './styles.module.css'


export default function Logo (): JSX.Element {
    return (
        <div className={styles.logo} tabIndex={-1}>
            <a href="/" aria-label="home">
              <div className={styles.hexContainer}>
                <HexIcon />
              </div>
              <div className={styles.logoContainer}>
                <LogoIcon />
              </div>
            </a>
        </div>
    )
  }
  

export const LogoIcon = dynamic<{ className?: string }>(
  async () => await import('./logo-icon.svg'),
  {
    loading: () => <span />,
    ssr: false
  }
)

export const HexIcon = dynamic<{ className?: string }>(
    async () => await import('./hex-icon.svg'),
    {
      loading: () => <span />,
      ssr: false
    }
  )
