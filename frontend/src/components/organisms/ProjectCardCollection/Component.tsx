import React from "react";
import styles from "./styles.module.css";

import type { ProjectDetail } from "@graphql/content-types/project/project";

import ProjectCard from "@components/molecules/ProjectCard/Component";

interface ProjectCardCollectionProps {
  projects: ProjectDetail[];
}

export default function ProjectCardCollection(
  props: ProjectCardCollectionProps
): JSX.Element {
  const { projects } = props;

  return (
    <ul className={styles.projectCollection}>
      {projects.map((item, key) => (
        <li key={key} className={styles.item}>
          <ProjectCard project={item} />
        </li>
      ))}
    </ul>
  );
}
