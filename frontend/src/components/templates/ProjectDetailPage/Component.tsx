import React, { useState } from 'react'
import styles from './styles.module.css'
import clsx from 'clsx'
import { hasValue } from '@misc/helpers'
import Link from 'next/link'
import Image from 'next/image'
import { useTranslation } from 'next-i18next'

import type { Technologies } from '@graphql/taxonomies/technologies/technology'
import type { ProjectDetail } from '@graphql/content-types/project/project'
import type { MediaImage } from '@graphql/media'

import gridStyles from '@components/atoms/Grid/styles.module.css'
import MainContainer from '@components/atoms/MainContainer/Component'
import { Button } from '@components/atoms/Button/Component'
import { Slider } from '@components/molecules/Slider/Component'
import NumberedHeading from '@components/atoms/NumberedHeading/Component'

interface ProjectDetailPageProps {
  project: ProjectDetail
}

export default function ProjectDetailPage (props: ProjectDetailPageProps): JSX.Element {
  const { project } = props
  const { t } = useTranslation('projects')
  const [activeImageIndex, setActiveImageIndex] = useState(0)

  return (
    <MainContainer maxWidth={1000} paddingBlockStart>
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
                {project.technologies.map((tech: Technologies, key: number) => (
                  <span className={styles.techTag} key={key}>{tech.name}</span>
                ))}
              </td>
            </tr>
          </tbody>
        </table>
        <div className={styles.description}>
          <p dangerouslySetInnerHTML={{ __html: project.description }} />
          <div className={styles.externalLinks}>
            {hasValue(project.externalLink) &&
              <Button as='link' href={project.externalLink} icon='external-link'>{t('button.viewWebsite')}</Button>}
            {hasValue(project.githubLink) &&
              <Button as='link' href={project.githubLink} icon='github'>{t('button.viewCode')}</Button>}
          </div>
        </div>
        {hasValue(project.screenshots) && (
          <div className={styles.screenshotsContainer}>
            <NumberedHeading>Screenshots</NumberedHeading>
            <Slider activeItemIndex={setActiveImageIndex}>
              {project.screenshots.map((screenshot: MediaImage, key: number) => (
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
              {project.screenshots.map((screenshot: MediaImage, key: number) => (
                <div key={key} className={clsx(styles.screenshotDetail, { [styles.active]: key === activeImageIndex })}>
                  <h3 className={styles.screenshotTitle}>{screenshot.alt}</h3>
                  <p>{screenshot.description}</p>
                </div>
              ))}
            </div>
          </div>
        )}
      </div>
      <hr className={styles.separator} />
      <div className={styles.actionsWrapper}>
        <Link href='/archive'>
          <Button as='button'>Back to Archive</Button>
        </Link>
        {hasValue(project.nextProjectId) && (
          <Link href={project.nextProjectId}>
            <Button as='button'>Next Project</Button>
          </Link>
        )}
      </div>
    </MainContainer>
  )
};
