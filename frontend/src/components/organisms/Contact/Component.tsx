import React from 'react'
import styles from './styles.module.css'
import Section, { Allign } from '@components/atoms/Section/Component'
import { Button } from '@components/atoms/Button/Component'

export default function Contact (): JSX.Element {

  return (
    <Section allign={Allign.center} maxWidth={600}>
      <h2 className={styles.numberedHeading}>What's Next?</h2>
      <h2 className={styles.title}>Get In Touch</h2>
      <p className={styles.text}>Although Im not currently looking for any new opportunities, my inbox is always open. Whether you have a question or just want to say hi, Ill try my best to get back to you!</p>
      <Button as="button" onClick={() => window.location.href = 'mailto:yorick.vd.venne@hotmail.nl'} size='large'>Say Hello</Button>
    </Section>
  )
}
