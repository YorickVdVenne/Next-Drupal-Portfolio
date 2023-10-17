import { initializeApollo } from '../src/lib/apolloClient'
import { ALL_PROJECTS_QUERY } from '../src/graphql/all_projects'
import { ApolloError } from '@apollo/client'
import archive from '@content/archive.json'
import siteMenus from '@content/siteMenus.json'

import MainContainer from '@components/atoms/MainContainer/Component'
import ArchivePage from '@components/templates/ArchivePage/Component'
import { GlobalPageProps } from './_app'
import { ArchiveData } from '@graphql/content-types/basic-page/archive'

interface Props extends GlobalPageProps {
  basicPage: ArchiveData
}

export default function Archive (props: Props): JSX.Element {

  return (
    <MainContainer paddingBlock maxWidth={1300}>
        <ArchivePage archiveData={props.basicPage} />
    </MainContainer>
  )
}

export async function getStaticProps() {
  const apolloClient = initializeApollo()

  try {
    const result = await apolloClient.query({
      query: ALL_PROJECTS_QUERY,
    })

    return {
      props: {
        initializeApolloState: apolloClient.cache.extract(),
        basicPage: result.basicPage,
        menus: result.menus.data
      },
      revalidate: 1
    }
  } catch (error) {
    if (error instanceof ApolloError) {
      console.error('Apollo Error:', error);
    } else {
      console.error('Error:', error);
    }

    return {
      props: {
        initialApolloState: apolloClient.cache.extract(),
        basicPage: archive.data.archive,
        menus: siteMenus.data
      },
      revalidate: 1,
    };
  }
}
