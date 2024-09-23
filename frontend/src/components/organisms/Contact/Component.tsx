import React from "react";
import styles from "./styles.module.css";

import type { ContactSection } from "@graphql/sections";

import Section, { Allign } from "@components/atoms/Section/Component";
import { Button } from "@components/atoms/Button/Component";
import NumberedHeading from "@components/atoms/NumberedHeading/Component";

interface ContactProps {
  contactData: ContactSection;
}

export default function Contact(props: ContactProps): JSX.Element {
  return (
    <Section allign={Allign.center} maxWidth={600}>
      <NumberedHeading id={props.contactData.bookmark} number={4} mono>
        {props.contactData.overlineTitle}
      </NumberedHeading>
      <h2 className={styles.title}>{props.contactData.title}</h2>
      <p className={styles.text}>{props.contactData.description}</p>
      <Button
        as="button"
        onClick={() => {
          window.location.href = props.contactData.button.link;
        }}
        size="large"
      >
        {props.contactData.button.text}
      </Button>
    </Section>
  );
}
