import { ALL_PROJECTS_QUERY } from "@graphql/all_projects";
import { initializeApollo } from "@lib/apolloClient";
import { serverSideTranslations } from "next-i18next/serverSideTranslations";
import { ApolloError } from "@apollo/client";
import siteMenus from '@content/siteMenus.json'
import projects from '@content/projects.json'

import type { GlobalPageProps } from "@pages/_app";
import type { GetStaticPaths, GetStaticProps } from "next";
import type { Project } from "@graphql/content-types/project/project";
import type { MetatagsFragment } from "@graphql/metatags";

import Metatags from "@components/molecules/Metatags/Component";

interface Props extends GlobalPageProps {
    project: Project
    metatags: MetatagsFragment
}

export default function Project (props: Props): JSX.Element {
    
    return (
        <>        
            <Metatags {...props.metatags} />
            {/* ProjectHeader big image */}
            {/* Back button */}
            {/* Project Title */}
            {/* Cool hr or line */}
            {/* left all the info */}
            {/* Right description */}
            {/* Screenshots with titles */}
            {/* Next project button */}
        </>
    )
}

export const getStaticProps: GetStaticProps = async (ctx) => {
    const apolloClient = initializeApollo()
  
    try {
      const result = await apolloClient.query({
        query: ALL_PROJECTS_QUERY
      })
  
      return {
        props: {
          ...(await serverSideTranslations(ctx.locale ?? 'en-US')),
          initializeApolloState: apolloClient.cache.extract(),
          project: result.project,
          menus: result.menus.data
        },
        revalidate: 1
      }
    } catch (error) {
      if (error instanceof ApolloError) {
        console.error('Apollo Error:', error)
      } else {
        console.error('Error:', error)
      }
  
      return {
        props: {
          ...(await serverSideTranslations(ctx.locale ?? 'en-US')),
          initialApolloState: apolloClient.cache.extract(),
          project: projects.data.projects.find(project => project.id === ctx.params?.slug),
          metatags: projects.data.metatags,
          menus: siteMenus.data
        },
        revalidate: 1
      }
    }
}

export const getStaticPaths: GetStaticPaths = async (ctx) => {
    return {
      paths: [],
      fallback: true
    }
}
