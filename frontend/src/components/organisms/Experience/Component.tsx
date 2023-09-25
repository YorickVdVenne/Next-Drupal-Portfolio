import React, { useMemo, useState } from 'react'
import styles from './styles.module.css'
import gridStyles from '@components/atoms/Grid/styles.module.css'
import Section from '@components/atoms/Section/Component'
import TabList from '@components/molecules/TabList/Component'
import TabPanel from '@components/molecules/TabPanel/Component'

export default function Experience (): JSX.Element {
  const listItems = ['iO', 'Burst']
  const data = [
    {
      "company": "iO",
      "role": "Frontend Developer",
      "range": "September 2022 - April 2023",
      "listContent": [
        "Something I did",
        "Another thing I did in this time",
        "Perhaps this is important to note aswell",
        "And this is the last thing"
      ]
    },
    {
      "company": "Burst",
      "role": "Trainee Developer",
      "range": "May 2018 - September 2022",
      "listContent": [
        "Different content",
        "Really different stuff",
        "Even more different stuff",
        "Not even close anymore now"
      ]
    }
  ]
  const [activeListItem, setActiveListItem] = useState(listItems[0])
  const activeListItemIndex = useMemo(() => {
    return listItems.indexOf(activeListItem)
  }, [activeListItem, listItems])

  return (
    <Section maxWidth={700}>
      <h2 className={styles.numberedHeading}>Experience</h2>
      <div className={gridStyles.grid}>
        <div className={styles.tabListWrapper}>
          <TabList items={listItems} activeItem={activeListItem} setActiveItem={setActiveListItem} activeItemIndex={activeListItemIndex}/>
        </div>
        <div className={styles.tabPannelWrapper}>
          <TabPanel data={data} activeIndex={activeListItemIndex} />
        </div>
      </div>
    </Section>
  )
}
