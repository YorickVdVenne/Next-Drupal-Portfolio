import React, { useEffect, useRef, useState } from 'react'
import styles from './styles.module.css'
import NavigationItems from '../NavigationItems/Component'
import {Helmet} from "react-helmet"
import { useOnClickOutside } from '@misc/useOnClickOutside';

export default function NavigationMenu (): JSX.Element {
  const [menuOpen, setMenuOpen] = useState(false);

  const hamBoxStyles: { [key: string]: string} = {
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
    '--inner-after-transition': `${menuOpen ? 'var(--ham-after-active)' : 'var(--ham-after)'}`,
  }

  const asideStyles: { [key: string]: string} = {
    '--aside-transform': `${menuOpen ? '0' : '100'}vw`,
    '--aside-visibility': `${menuOpen ? 'visible' : 'hidden'}`,
  }

  const onKeyDown = (e: { key: any; }) => {
    if (e.key === 'Escape' || e.key === 'Esc') {
      setMenuOpen(false);
    }
  };

  const onResize = () => {
    if (window.innerWidth > 768) {
      setMenuOpen(false);
    }
  };

  useEffect(() => {
    document.addEventListener('keydown', onKeyDown);
    window.addEventListener('resize', onResize);

    return () => {
      document.removeEventListener('keydown', onKeyDown);
      window.removeEventListener('resize', onResize);
    };
  }, []);

  const wrapperRef = useRef(null)
  useOnClickOutside(wrapperRef, () => setMenuOpen(false))
    
  return (
    <div className={styles.menu}>
      <Helmet>
        <html className={menuOpen ? 'blur' : ''} />
      </Helmet>
        
      <div ref={wrapperRef}>
        <button onClick={() => setMenuOpen(!menuOpen)} className={styles.hamburgerButton}>
          <div className={styles.hamBox}>
            <div className={styles.hamBoxInner} style={hamBoxStyles} />
          </div>
        </button>
        <aside className={styles.aside} aria-hidden={!menuOpen} tabIndex={menuOpen ? 1 : -1} style={asideStyles}>
          <NavigationItems setMenuOpen={setMenuOpen} />
        </aside>
      </div>
    </div>
  );
};
