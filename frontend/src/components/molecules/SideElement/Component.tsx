import React from 'react'
import styles from './styles.module.css'
import clsx from 'clsx'
import { IconMapper } from '@components/atoms/Icons/Component'
import { EmailItem, SocialItem } from '@graphql/menus'

export enum Orientation {
  left = 'left',
  right = 'right'
}

interface SideElementProps {
  orientation: Orientation
  content: SocialItem[] | EmailItem
}

export default function SideElement (props: SideElementProps): JSX.Element {
  const { orientation, content } = props

  return (
    <div
      className={clsx(styles.SideElement, {
        [styles.left]: orientation === Orientation.left,
        [styles.right]: orientation === Orientation.right
      })}
    >
      {Array.isArray(content)
        ? (
          <ul className={styles.socials}>
            {content.map((item, index) => (
              <li key={index}>
                <a href={item.url} target='_blank' title={item.label} rel='noreferrer'>{IconMapper(item.icon)}</a>
              </li>
            ))}
          </ul>
          )
        : (
          <div className={styles.mail}>
            <a href={content.url}>{content.email}</a>
          </div>
          )}
    </div>
  )
}
