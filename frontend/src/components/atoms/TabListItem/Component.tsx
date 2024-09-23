import React from "react";
import styles from "./styles.module.css";
import clsx from "clsx";

interface TabListItemProp {
  item: string;
  onClick: () => void;
  isActive: boolean;
}

export default function TabListItem(props: TabListItemProp): JSX.Element {
  const { item, onClick, isActive } = props;

  return (
    <button
      className={clsx(styles.tabListItem, { [styles.active]: isActive })}
      onClick={onClick}
    >
      {item}
    </button>
  );
}
