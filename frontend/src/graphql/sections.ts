import { Project } from "./content-types/project/project";
import { Button } from "./generic";
import { MainImage } from "./media";
import { Company } from "./taxonomies/company/company";
import { Job } from "./taxonomies/job/job";
import { Technologies } from "./taxonomies/technologies/technology";

export interface HeaderSection {
    introText: string;
    name: string;
    punchline: string;
    shortDescription: string;
    button: Button;
}

export interface AboutSection {
    title: string;
    bookmark: string;
    description: string;
    technologies: Technologies[];
    profileImage: MainImage;
}

export interface ExperienceSection {
    title: string;
    bookmark: string;
    companies: Company[];
    jobs: Job[];
}

export interface ProjectSection {
    title: string;
    bookmark: string;
    featuredProjects: Project[]
    projects: Project[];
}

export interface ContactSection {
    overlineTitle: string;
    title: string;
    bookmark: string;
    description: string;
    button: Button;
}
