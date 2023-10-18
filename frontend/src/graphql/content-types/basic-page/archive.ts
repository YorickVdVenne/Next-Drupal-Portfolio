import { MetatagsFragment } from "@graphql/metatags";
import { Project } from "../project/project";

export interface ArchiveData {
    title: string;
    shortText: string;
    projects: Project[];
    metatags: MetatagsFragment
}