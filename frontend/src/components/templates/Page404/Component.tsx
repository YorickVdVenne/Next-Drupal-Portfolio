import React from 'react'
import styles from './styles.module.css'
import MainContainer from '@components/atoms/MainContainer/Component';
import Section, { Allign } from '@components/atoms/Section/Component';
import { Button } from '@components/atoms/Button/Component';
import Metatags from '@components/molecules/Metatags/Component';
import { useTranslation } from 'next-i18next';
import Link from 'next/link';

export default function Page404 (): JSX.Element {
    const { t } = useTranslation('pageNotFound');

    return (
        <>        
            <Metatags
                description={t('404.metatags.description')}
                title={t('404.metatags.title')}
                og={{
                    description: t('404.metatags.description'),
                    title: t('404.metatags.title'),
                    image: t('404.metatags.image')
                }}
            />
            <MainContainer>
                <Section allign={Allign.center} fullHeight>
                    <div>
                        <h1 className={styles.title}>{t('404.title')}</h1>
                        <h2 className={styles.subTitle}>{t('404.subTitle')}</h2>
                        <Link href='/'>
                            <Button as='button' size='large'>{t('404.buttonText')}</Button>
                        </Link>
                    </div>
                </Section>
            </MainContainer>
        </>
    );
};
