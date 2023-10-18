import { initializeApollo } from '../src/lib/apolloClient'
import { ALL_PROJECTS_QUERY } from '../src/graphql/all_projects'
import { ApolloError } from '@apollo/client'
import archive from '@content/archive.json'
import siteMenus from '@content/siteMenus.json'

import MainContainer from '@components/atoms/MainContainer/Component'
import ArchivePage from '@components/templates/ArchivePage/Component'
import { GlobalPageProps } from './_app'
import { ArchiveData } from '@graphql/content-types/basic-page/archive'
import Metatags from '@components/molecules/Metatags/Component'
import { serverSideTranslations } from 'next-i18next/serverSideTranslations'
import { GetStaticProps } from 'next'

interface Props extends GlobalPageProps {
  basicPage: ArchiveData
}

export default function Archive (props: Props): JSX.Element {
  return (
    <>
      <Metatags {...props.basicPage.metatags} />
      <MainContainer paddingBlock maxWidth={1300}>
        <ArchivePage archiveData={props.basicPage} />
      </MainContainer>
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
        basicPage: result.basicPage,
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
        basicPage: archive.data.archive,
        menus: siteMenus.data
      },
      revalidate: 1
    }
  }
}
