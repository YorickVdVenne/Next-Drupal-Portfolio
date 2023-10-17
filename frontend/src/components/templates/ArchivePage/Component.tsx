import React from 'react'
import styles from './styles.module.css'

import ArchiveTable from '@components/organisms/ArchiveTable/Component';
import { ArchiveData } from '@graphql/content-types/basic-page/archive';

interface ArchivePageProps {
    archiveData: ArchiveData
}

export default function ArchivePage (props: ArchivePageProps): JSX.Element {
    
    return (
        <>
            <header>
                <h2>{props.archiveData.title}</h2>
                <h1>{props.archiveData.shortText}</h1>
            </header>
            <div className={styles.tableContainer}>
                <ArchiveTable content={props.archiveData.projects} />
            </div>
        </>
    );
};
