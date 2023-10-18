import React from 'react'
import styles from './styles.module.css'
import MainContainer from '@components/atoms/MainContainer/Component';
import Section, { Allign } from '@components/atoms/Section/Component';
import { Button } from '@components/atoms/Button/Component';
import Metatags from '@components/molecules/Metatags/Component';

export default function Page404 (): JSX.Element {

    return (
        <>        
            <Metatags
                description='404 page not found'
                title='404 page not found'
                og={{
                    description: '404 page not found',
                    title: '404 page not found',
                    image: '/../public/images/profile-image.png'
                }}
            />
            <MainContainer>
                <Section allign={Allign.center} fullHeight>
                    <div>
                        <h1 className={styles.title}>404</h1>
                        <h2 className={styles.subTitle}>Page Not Found</h2>
                        <Button as='button' size='large' onClick={() => window.location.href = '/'}>Go Home</Button>
                    </div>
                </Section>
            </MainContainer>
        </>
    );
};
