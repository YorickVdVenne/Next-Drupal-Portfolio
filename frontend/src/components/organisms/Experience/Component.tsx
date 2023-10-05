import React, { useMemo, useState } from 'react'
import styles from './styles.module.css'
import gridStyles from '@components/atoms/Grid/styles.module.css'
import Section from '@components/atoms/Section/Component'
import TabList from '@components/molecules/TabList/Component'
import TabPanel from '@components/molecules/TabPanel/Component'
import NumberedHeading from '@components/atoms/NumberedHeading/Component'
import sections from '@content/sections.json'

export default function Experience (): JSX.Element {
  const experience = sections.data.sections.experience
  const [activeListItem, setActiveListItem] = useState(experience.companyList[0])
  const activeListItemIndex = useMemo(() => {
    return experience.companyList.indexOf(activeListItem)
  }, [activeListItem, experience.companyList])

  return (
    <Section maxWidth={700}>
      <NumberedHeading id={experience.bookmark} number={2}>{experience.title}</NumberedHeading>
      <div className={gridStyles.grid}>
        <div className={styles.tabListWrapper}>
          <TabList items={experience.companyList} activeItem={activeListItem} setActiveItem={setActiveListItem} activeItemIndex={activeListItemIndex}/>
        </div>
        <div className={styles.tabPannelWrapper}>
          <TabPanel data={experience.companies} activeIndex={activeListItemIndex} />
        </div>
      </div>
    </Section>
  )
}
