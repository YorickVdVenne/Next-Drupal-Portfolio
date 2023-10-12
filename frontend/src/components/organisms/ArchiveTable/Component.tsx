import React from 'react'
import styles from './styles.module.css'
import { Project } from '@components/molecules/FeaturedListItem/Component'
import { IconMapper } from '@components/atoms/Icons/Component'

interface ArchiveTableProps {
    data: Array<Project>
}

export default function ArchiveTable (props: ArchiveTableProps): JSX.Element {
    const { data } = props
    const date = new Date()
    
    return (
        <table className={styles.archiveTable}>
            <thead>
                <tr>
                    <th>Year</th>
                    <th>Title</th>
                    <th>Made at</th>
                    <th>Built with</th>
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
                        <td className={styles.company}>
                            {item.brand}
                        </td>
                        <td className={styles.tech}>
                            {item.technologies.map((tech, key) => (
                                <span key={key}>{tech.name}<span className={styles.separator}>·</span></span>
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
