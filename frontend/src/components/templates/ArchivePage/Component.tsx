import React from "react";
import styles from "./styles.module.css";
import { useTranslation } from "next-i18next";

import type { ArchiveData } from "@graphql/content-types/basic-page/archive";

import ArchiveTable from "@components/organisms/ArchiveTable/Component";
import { Button } from "@components/atoms/Button/Component";

interface ArchivePageProps {
  archiveData: ArchiveData;
}

export default function ArchivePage(props: ArchivePageProps): JSX.Element {
  const { t } = useTranslation("archive");

  return (
    <>
      <header>
        <h2>{props.archiveData.title}</h2>
        <h1>{props.archiveData.shortText}</h1>
      </header>
      <div className={styles.tableContainer}>
        <ArchiveTable content={props.archiveData.projects} />
      </div>
      <Button
        className={styles.contactButton}
        as="button"
        onClick={() => {
          window.location.href = t("contactButton.link");
        }}
        size="large"
      >
        {t("contactButton.label")}
      </Button>
    </>
  );
}
