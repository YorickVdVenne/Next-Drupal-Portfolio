export interface MetatagsFragment {
  __typename?: "Metatags";
  title: string;
  description?: string;
  og: {
    __typename?: "MetatagsOG";
    title: string;
    description?: string;
    image?: string;
  };
}
