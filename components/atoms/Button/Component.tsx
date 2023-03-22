import clsx from "clsx";
import React from "react";
import styles from "./styles.module.css";

interface ButtonProps {
  children?: React.ReactNode;
}

export function Button(props: ButtonProps): JSX.Element {
  const { children } = props;

  return (
    <button
      className={clsx(styles.button, {
        [styles.button]: true,
      })}
    >
      {children !== undefined && (
        <span className={styles.text}>{children}</span>
      )}
    </button>
  );
}
