import React from 'react'
import styles from './styles.module.css'
import clsx from 'clsx'

import { IconMapper } from '@components/atoms/Icons/Component'
import { Project } from '@graphql/content-types/project/project'

interface ArchiveTableProps {
    content: Project[]
}

export default function ArchiveTable (props: ArchiveTableProps): JSX.Element {
    const { content } = props
    
    return (
        <table className={styles.archiveTable}>
            <thead>
                <tr>
                    <th>Year</th>
                    <th>Title</th>
                    <th className={styles.hideOnMobile}>Made at</th>
                    <th className={styles.hideOnMobile}>Built with</th>
                    <th>Links</th>
                </tr>
            </thead>
            <tbody>
                {content.map((item, key) => (
                    <tr key={key}>
                        <td className={styles.year}>
                            {item.year}
                        </td>
                        <td className={styles.title}>
                            {item.title}
                        </td>
                        <td className={clsx(styles.company, styles.hideOnMobile)}>
                            {item.madeAt}
                        </td>
                        <td className={clsx(styles.tech, styles.hideOnMobile)}>
                            {item.technologies.map((tech, key) => (
                                <span key={key}>{tech.name}<span className={clsx(styles.separator, {
                                    [styles.lastChild]: key === item.technologies.length - 1
                                })}>·</span></span>
                            ))}
                        </td>
                        <td className={styles.links}>
                            <div className={styles.linkContainer}>
                                {item.externalLink 
                                    ? <a className={styles.link} href={item.externalLink} target='_blank'>{IconMapper('external-link')}</a>
                                    : ""
                                }
                                {item.githubLink 
                                    ? <a className={styles.link} href={item.githubLink} target='_blank'>{IconMapper('github')}</a>
                                    : ""
                                }
                            </div>
                        </td>
                    </tr>
                ))}
            </tbody>
        </table>
    );
};
