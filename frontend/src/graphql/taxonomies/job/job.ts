export interface JobDescription {
    description: string;
  }

export interface Job {
    role: string;
    companyName: string;
    period: string;
    jobDescription: JobDescription[];
}