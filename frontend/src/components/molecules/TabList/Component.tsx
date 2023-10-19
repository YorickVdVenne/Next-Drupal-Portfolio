import React from 'react'
import styles from './styles.module.css'
import TabListItem from '@components/atoms/TabListItem/Component'

interface TabListProps {
  items: string[]
  activeItem: string
  setActiveItem: React.Dispatch<React.SetStateAction<string>>
  activeItemIndex: number
}

export default function TabList (props: TabListProps): JSX.Element {
  const { items, activeItem, setActiveItem, activeItemIndex } = props

  const hightlightStyles: Record<string, number> = {
    '--list-length': items.length,
    '--active-item-index': activeItemIndex
  }

  return (
    <div className={styles.tabList}>
      {items.map((item, key) => (
        <TabListItem key={key} item={item} onClick={() => {setActiveItem(item)}} isActive={activeItem === item} />
      ))}
      <div className={styles.highlight} style={hightlightStyles} />
    </div>
  )
}
