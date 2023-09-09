import { initializeApollo } from '../src/lib/apolloClient'
import { ALL_PROJECTS_QUERY } from '../src/graphql/all_projects'

import HomePageHero from '@components/organisms/HomePageHero/Component'
import { useQuery } from '@apollo/client'

export default function Home (): JSX.Element {
  const { data } = useQuery(ALL_PROJECTS_QUERY)

  console.log(data)

  return (
    <>
      <HomePageHero />
    </>
  )
}

export async function getStaticProps() {
  const apolloClient = initializeApollo()

  await apolloClient.query({
    query: ALL_PROJECTS_QUERY,
  })

  return {
    props: {
      initialApolloState: apolloClient.cache.extract(),
    },
    revalidate: 1,
  }
}
