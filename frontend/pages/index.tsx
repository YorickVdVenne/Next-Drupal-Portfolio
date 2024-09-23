import { useEffect } from "react";
import { initializeApollo } from "../src/lib/apolloClient";
import { ALL_PROJECTS_QUERY } from "../src/graphql/all_projects";
import { ApolloError } from "@apollo/client";
import { serverSideTranslations } from "next-i18next/serverSideTranslations";
import home from "@content/home.json";
import siteMenus from "@content/siteMenus.json";

import type { HomeData } from "@graphql/content-types/basic-page/home";
import type { GlobalPageProps } from "./_app";
import type { GetStaticProps } from "next";

import MainContainer from "@components/atoms/MainContainer/Component";
import Header from "@components/organisms/Header/Component";
import About from "@components/organisms/About/Component";
import Experience from "@components/organisms/Experience/Component";
import Featured from "@components/organisms/Featured/Component";
import Contact from "@components/organisms/Contact/Component";
import Projects from "@components/organisms/Projects/Component";
import Metatags from "@components/molecules/Metatags/Component";

interface Props extends GlobalPageProps {
  basicPage: HomeData;
}

export default function Home(props: Props): JSX.Element {
  useEffect(() => {
    const hash = window.location.hash;
    if (hash !== "") {
      const sectionId = hash.substring(1);

      setTimeout(() => {
        const section = document.getElementById(sectionId);
        if (section != null) {
          const offset = 100;
          window.scrollTo({
            top: section.offsetTop - offset,
            behavior: "smooth",
          });
        }
      }, 100);
    }
  }, []);

  return (
    <>
      <Metatags {...props.basicPage.metatags} />
      <MainContainer>
        <Header headerData={props.basicPage.sections.header} />
        <About aboutData={props.basicPage.sections.about} />
        <Experience experienceData={props.basicPage.sections.experience} />
        <Featured featuredData={props.basicPage.sections.projects} />
        <Projects projectData={props.basicPage.sections.projects.projects} />
        <Contact contactData={props.basicPage.sections.contact} />
      </MainContainer>
    </>
  );
}

export const getStaticProps: GetStaticProps = async (ctx) => {
  const apolloClient = initializeApollo();

  try {
    const result = await apolloClient.query({
      query: ALL_PROJECTS_QUERY,
    });

    return {
      props: {
        ...(await serverSideTranslations(ctx.locale ?? "en-US")),
        initializeApolloState: apolloClient.cache.extract(),
        basicPage: result.basicPage,
        menus: result.menus.data,
      },
      revalidate: 1,
    };
  } catch (error) {
    if (error instanceof ApolloError) {
      console.error("Apollo Error:", error);
    } else {
      console.error("Error:", error);
    }

    return {
      props: {
        ...(await serverSideTranslations(ctx.locale ?? "en-US")),
        initialApolloState: apolloClient.cache.extract(),
        basicPage: home.data,
        menus: siteMenus.data,
      },
      revalidate: 1,
    };
  }
};
