import React from "react";
import styles from "./styles.module.css";
import clsx from "clsx";

import InternalOrExternalLink from "@lib/link/Component";
import { hasValue } from "@misc/helpers";
import { IconMapper } from "../Icons/Component";

interface ButtonProps {
  children: React.ReactNode;
  variant?: "primary" | "secondary";
  size?: "large";
  icon?: string;
}

type Props =
  | ({ as: "link" } & ButtonProps &
      React.AnchorHTMLAttributes<HTMLAnchorElement>)
  | ({ as: "button" } & ButtonProps &
      React.ButtonHTMLAttributes<HTMLButtonElement>);

export function Button(props: Props): JSX.Element {
  const { children, variant, size, icon, ...componentProps } = props;

  const childComponents = (
    <>
      {hasValue(icon) && (
        <span className={styles.buttonIcon}>{IconMapper(icon)}</span>
      )}
      {children}
    </>
  );

  if (componentProps.as === "link") {
    const classes = clsx(
      componentProps.className,
      styles.link,
      variant === "secondary" ? styles.linkSecondary : styles.linkPrimary
    );
    const { as, className, ...linkProps } = componentProps;
    return (
      <InternalOrExternalLink className={classes} {...linkProps}>
        {childComponents}
      </InternalOrExternalLink>
    );
  } else {
    const classes = clsx(componentProps.className, styles.button, {
      [styles.largeButton]: size === "large",
    });
    const { as, className, ...buttonProps } = componentProps;
    return (
      <button className={classes} {...buttonProps}>
        {childComponents}
      </button>
    );
  }
}
