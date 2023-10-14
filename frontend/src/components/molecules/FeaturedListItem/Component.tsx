import React from 'react'
import styles from './styles.module.css'
import gridStyles from '@components/atoms/Grid/styles.module.css'
import clsx from 'clsx'
import Card from '@components/atoms/Card/Component'
import { IconMapper } from '@components/atoms/Icons/Component'
import Image from 'next/image'

export enum TextAlign {
  right = 'right',
  left = 'left'
}

export interface Tag {
  id?: number
  name: string
}

export interface Project {
  id: number
  title: string
  brand: string
  summary?: string
  description: string
  period: string
  mainImage: string
  screenshots?: string
  roles: Tag[]
  technologies: Tag[]
  codeLink?: null
  siteLink?: string
  featured: boolean
}

interface FeaturedListItemProps {
  item: Project
  textAlign: TextAlign
  overlineText: string
}


export default function FeaturedListItem (props: FeaturedListItemProps): JSX.Element {
  const { item, textAlign, overlineText } = props

  return (
    <li className={clsx(gridStyles.grid, styles.featuredItem)}>
      <div className={clsx(styles.projectContent, {
        [styles.contentLeft]: textAlign === TextAlign.left,
      })}>
        <p className={styles.projectOverline}>{overlineText}</p>
        <h4 className={styles.projectTitle}>{item.title}</h4>
        <Card hideOnMobile>{item.description}</Card>
        <ul className={clsx(styles.projectTech, {
            [styles.techLeft]: textAlign === TextAlign.left,
        })}>
          {item.technologies.map((tech, key) => (
            <li key={key} className={clsx(styles.techItem, {
              [styles.techItemLeft]: textAlign === TextAlign.left,
            })}>{tech.name}</li>
          ))}
        </ul>
        {item.siteLink ? (
          <div className={clsx(styles.projectLinks, {
            [styles.projectLinksLeft]: textAlign === TextAlign.left,
          })}>
            <a href={item.siteLink} target='__blank'>{IconMapper('external-link')}</a>
          </div>
        ): ''}
      </div>
      <div className={clsx(styles.projectImage, {
        [styles.imageRight]: textAlign === TextAlign.left 
      })}>
        <a href={item.siteLink ? item.siteLink : item.mainImage} target='__blank'>        
            <Image 
              src={item.mainImage}
              alt={item.title}
              className={styles.image}
              width={25}
              height={25}
            />
        </a>
      </div>
    </li>
  )
}
