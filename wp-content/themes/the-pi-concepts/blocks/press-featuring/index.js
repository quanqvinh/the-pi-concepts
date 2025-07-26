import { registerBlockType } from "@wordpress/blocks";
import { useSelect } from "@wordpress/data";
import { __ } from "@wordpress/i18n";
import { Spinner } from "@wordpress/components";

registerBlockType("thepi/press-featuring", {
  edit() {
    // Fetch 3 latest press_featuring posts
    const posts = useSelect(
      (select) =>
        select("core").getEntityRecords("postType", "press_featuring", {
          per_page: 3,
          _embed: true,
          order: "desc",
          orderby: "date",
        }),
      []
    );

    if (!posts) {
      return <Spinner />;
    }
    if (posts.length === 0) {
      return <p>{__("No press featuring posts found.", "the-pi-concepts")}</p>;
    }

    return (
      <div
        className="press-featuring-grid"
        style={{
          display: "grid",
          gridTemplateColumns: "repeat(3,1fr)",
          gap: "1.5em",
        }}
      >
        {posts.map((post) => (
          <div className="press-featuring-item" key={post.id}>
            <a
              href={post.meta?.press_featuring_link || "#"}
              target="_blank"
              rel="noopener"
            >
              {post._embedded?.["wp:featuredmedia"]?.[0]?.source_url && (
                <div className="press-featuring-thumb">
                  <img
                    src={post._embedded["wp:featuredmedia"][0].source_url}
                    alt={post.title.rendered}
                  />
                </div>
              )}
              <h3 className="press-featuring-title">{post.title.rendered}</h3>
              {post.meta?.press_featuring_subtitle && (
                <p className="subtitle">{post.meta.press_featuring_subtitle}</p>
              )}
            </a>
          </div>
        ))}
      </div>
    );
  },
  save() {
    // Dynamic block: output is rendered by PHP
    return null;
  },
});
