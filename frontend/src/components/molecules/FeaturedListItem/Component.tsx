import React from 'react'
import styles from './styles.module.css'
import clsx from 'clsx'
import Image from 'next/image'
import { useTranslation } from 'next-i18next'
import { hasValue } from '@misc/helpers'
import Link from 'next/link'

import type { ProjectDetail } from '@graphql/content-types/project/project'

import gridStyles from '@components/atoms/Grid/styles.module.css'
import { IconMapper } from '@components/atoms/Icons/Component'
import Card from '@components/atoms/Card/Component'
import { Button } from '@components/atoms/Button/Component'

export enum TextAlign {
  right = 'right',
  left = 'left'
}

interface FeaturedListItemProps {
  item: ProjectDetail
  textAlign: TextAlign
}

export default function FeaturedListItem (props: FeaturedListItemProps): JSX.Element {
  const { t } = useTranslation(['projects', 'common'])
  const { item, textAlign } = props

  return (
    <li className={clsx(gridStyles.grid, styles.featuredItem)}>
      <div className={clsx(styles.projectContent, { [styles.contentLeft]: textAlign === TextAlign.left })}>
        <p className={styles.projectOverline}>{t('featuredProjects.overlineText')}</p>
        <Link href={`/projects/${item.id}`}>
          <h4 className={styles.projectTitle}>{item.title}</h4>
        </Link>
        <Card hideOnMobile>{item.summary}</Card>
        <ul className={clsx(styles.projectTech, {
          [styles.techLeft]: textAlign === TextAlign.left
        })}
        >
          {item.technologies.map((tech, key) => (
            <li
              key={key} className={clsx(styles.techItem, {
                [styles.techItemLeft]: textAlign === TextAlign.left
              })}
            >{tech.name}
            </li>
          ))}
        </ul>
        <div className={clsx(styles.itemActions, { [styles.itemActionsLeft]: textAlign === TextAlign.left })}>
          <div className={clsx(styles.projectLinks, { [styles.projectLinksLeft]: textAlign === TextAlign.left })}>
            {hasValue(item.externalLink) &&
              <a className={styles.link} href={item.externalLink} target='__blank'>{IconMapper('external-link')}</a>}
            {hasValue(item.githubLink) &&
              <a className={styles.link} href={item.githubLink} target='__blank'>{IconMapper('github')}</a>}
          </div>
          <div className={clsx(styles.buttonWrapper, { [styles.buttonWrapperLeft]: textAlign === TextAlign.left })}>
            <Link href={`/projects/${item.id}`}>
              <Button className={styles.readMoreButton} as='button'>{t('button.readMore', { ns: 'common' })}</Button>
            </Link>
          </div>
        </div>
      </div>
      <div className={clsx(styles.projectImage, { [styles.imageRight]: textAlign === TextAlign.left })}>
        <Link href={`/projects/${item.id}`}>
          <Image
            src={item.mainImage.url}
            alt={item.mainImage.alt}
            className={styles.image}
            width={1000}
            height={1000}
          />
        </Link>
      </div>
    </li>
  )
}
