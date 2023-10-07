import React, { useState } from 'react'
import styles from './styles.module.css'

export default function NavigationMenu (): JSX.Element {
  const [menuOpen, setMenuOpen] = useState(false);

  const hamBoxStyles = {
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
    
    return (
      <div className={styles.menu}>
        <div>
          <button onClick={() => setMenuOpen(!menuOpen)} className={styles.hamburgerButton}>
            <div className={styles.hamBox}>
              <div className={styles.hamBoxInner} style={hamBoxStyles} />
            </div>
          </button>
          <aside></aside>
        </div>
      </div>
    );
};
