import React, { useMemo } from 'react'
import styles from './styles.module.css'
import TabListItem from '@components/atoms/TabListItem/Component'

interface TabListProps {
  items: string[]
  activeItem: string
  setActiveItem: Function
  activeItemIndex: number
}

export default function TabList (props: TabListProps): JSX.Element {
  const { items, activeItem, setActiveItem, activeItemIndex } = props

  return (
    <div className={styles.tabList}>
      {items.map((item, key) => (
        <TabListItem key={key} item={item} onClick={(item: string) => setActiveItem(item)} isActive={activeItem === item} />
      ))}
      <div className={styles.highlight} style={{
        transform: `translateY(${activeItemIndex*4.2}rem)`
      }} />
    </div>
  )
}
