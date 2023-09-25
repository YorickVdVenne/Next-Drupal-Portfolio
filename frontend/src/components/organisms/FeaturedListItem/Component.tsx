import React from 'react'
import styles from './styles.module.css'
import gridStyles from '@components/atoms/Grid/styles.module.css'
import clsx from 'clsx'
import Card from '@components/atoms/Card/Component'
import { IconMapper } from '@components/atoms/Icons/Component'
import Image from 'next/image'
import profileImage from '../../../../public/images/profile-image.png'

export enum TextAlign {
  right = 'right',
  left = 'left'
}

interface FeaturedListItemProps {
  textAlign: TextAlign
}


export default function FeaturedListItem (props: FeaturedListItemProps): JSX.Element {
  const { textAlign } = props

  return (
    <li className={clsx(gridStyles.grid, styles.featuredItem)}>
      <div className={clsx(styles.projectContent)}>
        <p className={styles.projectOverline}>Featured Project</p>
        <h4 className={styles.projectTitle}>Aviko QR</h4>
        <Card>Description of project</Card>
        <ul className={styles.projectTech}>
          <li>Drupal</li>
          <li>Node.js</li>
          <li>JavaScript</li>
          <li>PHP</li>
        </ul>
        <div className={styles.projectLinks}>
          <a href='https://qr.avikofoodservice.com/' target='__blank'>{IconMapper('external-link')}</a>
        </div>
      </div>
      <div className={styles.projectImage}>
        <a href='https://qr.avikofoodservice.com/' target='__blank'>        
          <div className={styles.imageWrapper}>
            <Image 
              src={profileImage}
              alt='Picture of Yorick'
              className={styles.image}
            />
          </div>
        </a>
      </div>
    </li>
  )
}
