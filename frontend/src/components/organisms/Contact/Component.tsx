import React from 'react'
import styles from './styles.module.css'
import Section, { Allign } from '@components/atoms/Section/Component'
import { Button } from '@components/atoms/Button/Component'
import NumberedHeading from '@components/atoms/NumberedHeading/Component'
import sections from '@content/sections.json'

export default function Contact (): JSX.Element {

  const contact = sections.data.sections.contact

  return (
    <Section allign={Allign.center} maxWidth={600}>
      <NumberedHeading id={contact.bookmark} number={4} mono>{contact.numberedTitle}</NumberedHeading>
      <h2 className={styles.title}>{contact.title}</h2>
      <p className={styles.text}>{contact.description}</p>
      <Button as="button" onClick={() => window.location.href = contact.button.src} size='large'>{contact.button.title}</Button>
    </Section>
  )
}
