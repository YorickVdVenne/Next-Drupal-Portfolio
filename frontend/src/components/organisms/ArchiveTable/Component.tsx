import React from 'react'
import styles from './styles.module.css'
import clsx from 'clsx'

import { Project } from '@components/molecules/FeaturedListItem/Component'
import { IconMapper } from '@components/atoms/Icons/Component'

interface ArchiveTableProps {
    data: Array<Project>
}

export default function ArchiveTable (props: ArchiveTableProps): JSX.Element {
    const { data } = props
    
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
                {data.map((item, key) => (
                    <tr key={key}>
                        <td className={styles.year}>
                            {item.period.match(/\d{4}/)}
                        </td>
                        <td className={styles.title}>
                            {item.title}
                        </td>
                        <td className={clsx(styles.company, styles.hideOnMobile)}>
                            {item.brand}
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
                                {item.siteLink 
                                    ? <a className={styles.link} href={item.siteLink} target='_blank'>{IconMapper('external-link')}</a>
                                    : ""
                                }
                                {item.codeLink 
                                    ? <a className={styles.link} href={item.codeLink} target='_blank'>{IconMapper('github')}</a>
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
