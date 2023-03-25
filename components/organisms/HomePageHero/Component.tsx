import clsx from 'clsx'
import React from 'react'
import Container from '@components/atoms/Container/Component'
import styles from './styles.module.css'

export default function HomePageHero (): JSX.Element {
  return (
    <div className={styles.hero}>
      <Container>
        <h1>Frontend Developer</h1>
        <h3>Yorick Van de Venne</h3>
        <h2>I'm a frontend web developer based in Rotterdam, The Netherlands</h2>
        <p>
          Some nice text about me, even more. Some nice text about me, even more. Some nice text about me, even more. Some nice text about me, even more.
          Some nice text about me, even more. Some nice text about me, even more. Some nice text about me, even more.
        </p>
        <p>
          Some nice text about me, even more. Some nice text about me, even more. Some nice text about me, even more. Some nice text about me, even more.
          Some nice text about me, even more. Some nice text about me, even more. Some nice text about me, even more.
        </p>
      </Container>
    </div>
  )
}
