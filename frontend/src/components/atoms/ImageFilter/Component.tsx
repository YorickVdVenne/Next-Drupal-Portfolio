import React from 'react'
import styles from './styles.module.css'
import Image from 'next/image'

import type { MediaImage } from '@graphql/media'

interface ImageFilterProps {
  image: MediaImage
}

export default function ImageFilter (props: ImageFilterProps): JSX.Element {
  const { image } = props

  return (
    <div className={styles.imageWrapper}>
      <Image
        src={image.url}
        alt={image.alt}
        className={styles.image}
        width={1000}
        height={1000}
      />
    </div>
  )
};
