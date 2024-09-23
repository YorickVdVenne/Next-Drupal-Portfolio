import React from "react";
import styles from "./styles.module.css";
import { useTranslation } from "next-i18next";

import type { ProjectDetail } from "@graphql/content-types/project/project";

import Section, { Allign } from "@components/atoms/Section/Component";
import { Button } from "@components/atoms/Button/Component";
import ProjectCardCollection from "../ProjectCardCollection/Component";

interface ProjectsProps {
  projectData: ProjectDetail[];
}

export default function Projects(props: ProjectsProps): JSX.Element {
  const { t } = useTranslation("projects");

  return (
    <Section allign={Allign.center}>
      <h3 className={styles.title}>{t("otherProjects.title")}</h3>
      <Button
        as="link"
        href={t("otherProjects.archiveButton.link")}
        className={styles.link}
      >
        {t("otherProjects.archiveButton.label")}
      </Button>
      <ProjectCardCollection projects={props.projectData} />
      {/* <Button as='button' size='large' className={styles.button}>Show More</Button> */}
    </Section>
  );
}
