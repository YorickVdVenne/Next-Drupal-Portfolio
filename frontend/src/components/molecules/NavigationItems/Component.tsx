import React from 'react'
import styles from './styles.module.css'
import { Button } from '@components/atoms/Button/Component';
import clsx from 'clsx'
import { ActionButton, MenuItem } from '@graphql/menus';
import Link from 'next/link';

interface NavigationItemsProps {
  links: MenuItem[]
  actionButton: ActionButton
  desktop?: boolean
  setMenuOpen?: Function
}

export default function NavigationItems (props: NavigationItemsProps): JSX.Element {
  const { links, actionButton, desktop, setMenuOpen } = props
    
  return (
    <div className={clsx(styles.navItems, {[styles.desktop]: desktop})}>
      <ol className={clsx({[styles.desktop]: desktop})}>       
        {links.map((link, key) => (
          <li key={key} className={clsx({[styles.desktop]: desktop})}>
            <Link 
              href={link.url} 
              className={clsx({[styles.desktop]: desktop})}
              onClick={(e) => {
                const sectionId = link.url.replace('/#', '')
                const section = document.getElementById(sectionId)
                if (section) {
                  e.preventDefault()
                  window.scrollTo({
                    top: section.offsetTop - 100,
                    behavior: 'smooth' 
                  })
                  if (setMenuOpen) setMenuOpen(false)
                }
              }}
            >
              {link.label}
            </Link>
          </li>
        ))}
      </ol>
      <Link href={actionButton.url}>        
        <Button 
          className={clsx(styles.button, {[styles.desktop]: desktop})} 
          as='button' 
          size={!desktop ? 'large': undefined}
        >
          {actionButton.label}
        </Button>
      </Link>
    </div>
  );
};
