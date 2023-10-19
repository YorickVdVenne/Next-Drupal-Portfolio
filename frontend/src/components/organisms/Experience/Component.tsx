import React, { useMemo, useState } from 'react'
import styles from './styles.module.css'

import type { ExperienceSection } from '@graphql/sections'

import gridStyles from '@components/atoms/Grid/styles.module.css'
import Section from '@components/atoms/Section/Component'
import TabList from '@components/molecules/TabList/Component'
import TabPanel from '@components/molecules/TabPanel/Component'
import NumberedHeading from '@components/atoms/NumberedHeading/Component'

interface ExperienceProps {
  experienceData: ExperienceSection
}

export default function Experience (props: ExperienceProps): JSX.Element {
  const [activeListItem, setActiveListItem] = useState(props.experienceData.companies[0].name)
  const activeListItemIndex = useMemo(() => {
    return props.experienceData.companies.map((company) => company.name).indexOf(activeListItem)
  }, [activeListItem, props.experienceData.companies])

  return (
    <Section maxWidth={700}>
      <NumberedHeading id={props.experienceData.bookmark} number={2}>{props.experienceData.title}</NumberedHeading>
      <div className={gridStyles.grid}>
        <div className={styles.tabListWrapper}>
          <TabList
            items={props.experienceData.companies.map((company) => company.name)}
            activeItem={activeListItem}
            setActiveItem={setActiveListItem}
            activeItemIndex={activeListItemIndex}
          />
        </div>
        <div className={styles.tabPannelWrapper}>
          <TabPanel data={props.experienceData.jobs} activeIndex={activeListItemIndex} />
        </div>
      </div>
    </Section>
  )
}
