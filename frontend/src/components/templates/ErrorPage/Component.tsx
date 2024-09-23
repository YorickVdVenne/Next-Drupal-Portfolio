import React from "react";
import styles from "./styles.module.css";
import { useTranslation } from "next-i18next";
import Link from "next/link";

import MainContainer from "@components/atoms/MainContainer/Component";
import Section, { Allign } from "@components/atoms/Section/Component";
import { Button } from "@components/atoms/Button/Component";
import Metatags from "@components/molecules/Metatags/Component";

export default function ErrorPage(): JSX.Element {
  const { t } = useTranslation("errors");

  return (
    <>
      <Metatags
        description={t("500.metatags.description")}
        title={t("500.metatags.title")}
        og={{
          description: t("500.metatags.description"),
          title: t("500.metatags.title"),
          image: t("500.metatags.image"),
        }}
      />
      <MainContainer>
        <Section allign={Allign.center} fullHeight>
          <div>
            <h1 className={styles.title}>{t("500.title")}</h1>
            <h2 className={styles.subTitle}>{t("500.subTitle")}</h2>
            <Link href="/">
              <Button as="button" size="large">
                {t("500.buttonText")}
              </Button>
            </Link>
          </div>
        </Section>
      </MainContainer>
    </>
  );
}
