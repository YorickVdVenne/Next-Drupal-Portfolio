import React from 'react'
import Container from '@components/atoms/Container/Component'
import styles from './styles.module.css'
import { Button } from '@components/atoms/Button/Component'

export default function HomePageHero (): JSX.Element {

  return (
    <div className={styles.hero}>
      <Container>
        <h1>Hi, my name is</h1>
        <h2>Yorick Van de Venne.</h2>
        <h3>Frontend developer</h3>
        <p>
          Some nice text about me, even more. Some nice text about me, even more. Some nice text about me, even more. Some nice text about me, even more.
          Some nice text about me, even more. Some nice text about me, even more. Some nice text about me, even more.
        </p>
        <p>
          Some nice text about me, even more. Some nice text about me, even more. Some nice text about me, even more. Some nice text about me, even more.
          Some nice text about me, even more. Some nice text about me, even more. Some nice text about me, even more. <Button as='link' onClick={() => console.log('test')}>Link</Button>
        </p>
        <Button onClick={() => console.log('test')} as='button'>Resume</Button>
      </Container>
    </div>
  )
}
