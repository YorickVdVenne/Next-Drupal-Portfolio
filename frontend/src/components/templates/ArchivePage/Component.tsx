import React from 'react'
import styles from './styles.module.css'

import ArchiveTable from '@components/organisms/ArchiveTable/Component';
import projects from '@content/projects.json'

interface ArchivePageProps {
    // prop: string
}

export default function ArchivePage (props: ArchivePageProps): JSX.Element {
    //  const { prop } = props
    
    return (
        <>
            <header>
                <h2>Archive</h2>
                <h1>A big list of things I've worked on</h1>
            </header>
            <div className={styles.tableContainer}>
                <ArchiveTable data={projects.data.projects.items} />
            </div>
        </>
    );
};
