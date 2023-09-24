import React from 'react'
import styles from './styles.module.css'
import clsx from 'clsx'
import { IconMapper } from '@components/atoms/Icons/Component'

export enum Orientation {
    left = 'left',
    right = 'right'
}

export enum DisplayOption {
    socials = 'socials',
    mail = 'mail'
}

interface SideElementProps {
    orientation: Orientation
    displayOption: DisplayOption
}

export default function SideElement (props: SideElementProps): JSX.Element {
  const { orientation, displayOption } = props

  return (
    <div className={clsx(styles.SideElement, {
        [styles.left]: orientation === Orientation.left,
        [styles.right]: orientation === Orientation.right
    })}>
        {displayOption === DisplayOption.socials ? (
            <ul className={styles.socials}>
                <li><a href='https://github.com/YorickVdVenne'>{IconMapper('github')}</a></li>
                <li><a href='https://www.linkedin.com/in/yorick-van-de-venne-514121186'>{IconMapper('linkedin')}</a></li>
                <li><a href='https://codepen.io/YVenne'>{IconMapper('codepen')}</a></li>
            </ul>
        ): displayOption === DisplayOption.mail ? (
            <div className={styles.mail}>
                <a href='mailto:yorick.vd.venne@hotmail.nl'>yorick.vd.venne@hotmail.nl</a>
            </div>
        ): ''}
    </div>
  )
}
