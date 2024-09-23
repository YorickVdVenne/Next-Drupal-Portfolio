import dynamic from "next/dynamic";
import React from "react";

export const Codepen = dynamic<{ className?: string }>(
  async () => await import("@icons/codepen.svg"),
  {
    loading: () => <span />,
    ssr: false,
  }
);

export const ExternalLink = dynamic<{ className?: string }>(
  async () => await import("@icons/external-link.svg"),
  {
    loading: () => <span />,
    ssr: false,
  }
);

export const Folder = dynamic<{ className?: string }>(
  async () => await import("@icons/folder.svg"),
  {
    loading: () => <span />,
    ssr: false,
  }
);

export const Github = dynamic(async () => await import("@icons/github.svg"), {
  loading: () => <span />,
  ssr: false,
});

export const LinkedIn = dynamic<{ className?: string }>(
  async () => await import("@icons/linkedin.svg"),
  {
    loading: () => <span />,
    ssr: false,
  }
);

export const Miro = dynamic<{ className?: string }>(
  async () => await import("@icons/miro.svg"),
  {
    loading: () => <span />,
    ssr: false,
  }
);

export const Arrow = dynamic<{ className?: string }>(
  async () => await import("@icons/arrow.svg"),
  {
    loading: () => <span />,
    ssr: false,
  }
);

export function IconMapper(icon: string): React.ReactElement | null {
  switch (icon) {
    case "codepen":
      return <Codepen />;

    case "external-link":
      return <ExternalLink />;

    case "folder":
      return <Folder />;

    case "github":
      return <Github />;

    case "linkedin":
      return <LinkedIn />;

    case "miro":
      return <Miro />;

    default:
      return null;
  }
}
