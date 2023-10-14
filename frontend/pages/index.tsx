import { initializeApollo } from '../src/lib/apolloClient'
import { ALL_PROJECTS_QUERY } from '../src/graphql/all_projects'
import { ApolloError } from '@apollo/client';

import MainContainer from '@components/atoms/MainContainer/Component'
import Header from '@components/organisms/Header/Component'
import About from '@components/organisms/About/Component'
import Experience from '@components/organisms/Experience/Component'
import Featured from '@components/organisms/Featured/Component'
import Contact from '@components/organisms/Contact/Component'
import Projects from '@components/organisms/Projects/Component'

interface Props {
  data: string
}

export default function Home (props: Props): JSX.Element {

  console.log(props.data)

  return (
    <MainContainer>
      <Header />
      <About />
      <Experience />
      <Featured />
      <Projects />
      <Contact />
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
        data: result.data,
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
        data: {}, // Provide default data or an empty object
      },
      revalidate: 1,
    };
  }
}
