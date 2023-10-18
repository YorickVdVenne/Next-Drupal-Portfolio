import React from 'react'
import styles from './styles.module.css'

import ArchiveTable from '@components/organisms/ArchiveTable/Component';
import { ArchiveData } from '@graphql/content-types/basic-page/archive';
import { Button } from '@components/atoms/Button/Component';
import { useTranslation } from 'next-i18next';

interface ArchivePageProps {
    archiveData: ArchiveData
}

export default function ArchivePage (props: ArchivePageProps): JSX.Element {
    const { t } = useTranslation('archive');
    
    return (
        <>
            <header>
                <h2>{props.archiveData.title}</h2>
                <h1>{props.archiveData.shortText}</h1>
            </header>
            <div className={styles.tableContainer}>
                <ArchiveTable content={props.archiveData.projects} />
            </div>
            <p className={styles.message}>{t('message')}</p>
            <Button as="button" onClick={() => window.location.href = t('contactButton.link')} size='large'>{t('contactButton.label')}</Button>
        </>
    );
};
