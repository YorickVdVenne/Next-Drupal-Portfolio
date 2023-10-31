import React, { useState } from 'react'
import styles from './styles.module.css'
import clsx from 'clsx'

import type{ Project } from '@graphql/content-types/project/project'
import Image from 'next/image'
import MainContainer from '@components/atoms/MainContainer/Component'
import { useTranslation } from 'next-i18next'
import gridStyles from '@components/atoms/Grid/styles.module.css'
import { hasValue } from '@misc/helpers'
import { Button } from '@components/atoms/Button/Component'
import { Slider } from '@components/molecules/Slider/Component'
import NumberedHeading from '@components/atoms/NumberedHeading/Component'

interface ProjectDetailPageProps {
  project: Project
}

export default function ProjectDetailPage (props: ProjectDetailPageProps): JSX.Element {
  const { project } = props
  const { t } = useTranslation('projects')
  const [activeImageIndex, setActiveImageIndex] = useState(0);

  return (
    <MainContainer maxWidth={1000} paddingBlock>
      <h2 className={styles.projectTitle}>{project.title}</h2>
      <div className={styles.mainImageWrapper}>
        <Image src={project.mainImage.url} alt={project.mainImage.alt} className={styles.mainImage} width={1000} height={1000} />
      </div>
      <div className={clsx(gridStyles.grid, styles.content)}>

        <table className={styles.projectTable}>
          <tbody>
            <tr>
              <th>{t('project.year')}</th>
              <td>{project.year}</td>
            </tr>
            <tr>
              <th>{t('project.client')}</th>
              <td>{project.madeFor}</td>
            </tr>
            <tr>
              <th>{t('project.madeAt')}</th>
              <td>{project.madeAt}</td>
            </tr>
            <tr>
              <th colSpan={2}>{t('project.technologies')}</th>
            </tr>
            <tr>
              <td className={styles.techData} colSpan={2}>
                {project.technologies.map((tech, key) => (
                  <span className={styles.techTag} key={key}>{tech.name}</span>
                ))}
              </td>
            </tr>
          </tbody>
        </table>
        <div className={styles.description}>
          <p>
            {project.description}
          </p>
          {hasValue(project.externalLink) 
            ? <Button as='link' href={project.externalLink} icon='external-link' >View the website</Button>
            : ''}
        </div>
        {hasValue(project.screenshots) && (
          <div className={styles.screenshotsContainer}>
            <NumberedHeading>Screenshots</NumberedHeading>
            <Slider activeItemIndex={setActiveImageIndex}>
              {project.screenshots.map((screenshot, key) => (
                <div 
                  className={styles.screenshotImageWrapper} 
                  key={key}
                >                    
                  <Image 
                    className={styles.screenshotImage}
                    src={screenshot.url} 
                    alt={screenshot.alt} 
                    width={1000}
                    height={1000} 
                  />
                </div>
              ))}
            </Slider>
            <div className={styles.screenshotDetails}>
              {project.screenshots.map((screenshot, key) => (
                <div key={key} className={clsx(styles.screenshotDetail, {[styles.active]: key === activeImageIndex})}>                
                  <h3 className={styles.screenshotTitle}>{screenshot.alt}</h3>
                  <p>{screenshot.description}</p>
                </div>
              ))}
            </div>
          </div>
         )}
      </div>
      <div className={styles.buttonWrapper}>
        <Button className={styles.nextProjectButton} as='button'>Next Project</Button>
      </div>
    </MainContainer>
  )
};
