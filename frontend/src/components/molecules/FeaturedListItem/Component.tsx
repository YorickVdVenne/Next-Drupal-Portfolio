import React from 'react'
import styles from './styles.module.css'
import gridStyles from '@components/atoms/Grid/styles.module.css'
import clsx from 'clsx'
import Card from '@components/atoms/Card/Component'
import { IconMapper } from '@components/atoms/Icons/Component'
import Image from 'next/image'
import { Project } from '@graphql/content-types/project/project'
import { Button } from '@components/atoms/Button/Component'
import { useTranslation } from 'next-i18next'

export enum TextAlign {
  right = 'right',
  left = 'left'
}

interface FeaturedListItemProps {
  item: Project
  textAlign: TextAlign
}


export default function FeaturedListItem (props: FeaturedListItemProps): JSX.Element {
  const { t } = useTranslation('projects');
  const { item, textAlign } = props

  return (
    <li className={clsx(gridStyles.grid, styles.featuredItem)}>
      <div className={clsx(styles.projectContent, {
        [styles.contentLeft]: textAlign === TextAlign.left,})}
      >
        <p className={styles.projectOverline}>{t('featuredProjects.overlineText')}</p>
        <h4 className={styles.projectTitle}>{item.title}</h4>
        <Card hideOnMobile>{item.summary}</Card>
        <ul className={clsx(styles.projectTech, {
            [styles.techLeft]: textAlign === TextAlign.left,
        })}>
          {item.technologies.map((tech, key) => (
            <li key={key} className={clsx(styles.techItem, {
              [styles.techItemLeft]: textAlign === TextAlign.left,
            })}>{tech.name}</li>
          ))}
        </ul>
        <div className={clsx(styles.projectLinks, {
          [styles.projectLinksLeft]: textAlign === TextAlign.left })}
        >
          {item.externalLink 
            ? <a href={item.externalLink} target='__blank'>{IconMapper('external-link')}</a>
            : ''
          }
          {item.githubLink 
            ? <a href={item.githubLink} target='__blank'>{IconMapper('github')}</a>
            : ''
          }
          {/* <Button as='button'>Read more</Button> */}
        </div>
      </div>
      <div className={clsx(styles.projectImage, {
        [styles.imageRight]: textAlign === TextAlign.left })}
      >
        <a href={item.externalLink ? item.externalLink : '/'} target='__blank'>        
            <Image 
              src={item.mainImage.url}
              alt={item.mainImage.alt}
              className={styles.image}
              width={1000}
              height={1000}
            />
        </a>
      </div>
    </li>
  )
}
