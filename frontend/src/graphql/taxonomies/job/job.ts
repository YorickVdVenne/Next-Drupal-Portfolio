export interface JobDescription {
  description: string
}

export interface Job {
  role: string
  companyName: string
  link: string
  period: string
  jobDescription: JobDescription[]
}
