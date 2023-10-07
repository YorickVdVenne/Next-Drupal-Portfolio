import React from 'react'
import styles from './styles.module.css'
import menu from '@content/menu.json'
import { Button } from '@components/atoms/Button/Component';
import clsx from 'clsx'

interface NavigationItemsProps {
  desktop?: boolean
  setMenuOpen?: Function
}

export default function NavigationItems (props: NavigationItemsProps): JSX.Element {
  const { desktop, setMenuOpen } = props
    
    return (
      <div className={clsx(styles.navItems, {[styles.desktop]: desktop})}>
        <ol className={clsx({[styles.desktop]: desktop})}>       
          {menu.menu.links.map((link, key) => (
            <li key={key} className={clsx({[styles.desktop]: desktop})}>
              <a onClick={() => setMenuOpen ? setMenuOpen(false) : ''} className={clsx({[styles.desktop]: desktop})} href={link.url}>{link.label}</a>
            </li>
          ))}
        </ol>
        <Button 
          className={clsx(styles.button, {[styles.desktop]: desktop})} 
          as='button' 
          onClick={() => window.location.href = menu.menu.actionButton.url}
          size={!desktop ? 'large': undefined}
        >
          {menu.menu.actionButton.label}
        </Button>
      </div>
    );
};
