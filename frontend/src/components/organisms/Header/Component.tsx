import React from "react";
import styles from "./styles.module.css";

import type { HeaderSection } from "@graphql/sections";

import { Button } from "@components/atoms/Button/Component";

interface HeaderProps {
  headerData: HeaderSection;
}

export default function Header(props: HeaderProps): JSX.Element {
  return (
    <header className={styles.header}>
      <h1 className={styles.h1}>{props.headerData.introText}</h1>
      <h2 className={styles.h2}>{props.headerData.name}</h2>
      <h3>{props.headerData.punchline}</h3>
      <div className={styles.wrapper}>
        <p className={styles.text}>{props.headerData.shortDescription}</p>
        <Button
          onClick={() => window.open(props.headerData.button.link, "_blank")}
          as="button"
          size="large"
          className={styles.button}
        >
          {props.headerData.button.text}
        </Button>
      </div>
    </header>
  );
}
