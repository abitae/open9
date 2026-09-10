import { j as e } from "./index-BHPdvlM3.js";

const HERO_PHOTO =
  "https://images.unsplash.com/photo-1600880292089-90a7e086ee0c?auto=format&fit=crop&w=900&h=640&q=80";

function PageHeader({ label, title, description, children }) {
  return e.jsx("section", {
    className: "page-hero-shell",
    children: e.jsx("div", {
      className: "mx-auto max-w-7xl",
      children: e.jsxs("div", {
        className: "liquid-glass-strong page-hero-card glow-tech relative overflow-hidden rounded-[1.75rem] border border-white/5 px-5 py-6 lg:px-8 lg:py-7",
        children: [
          e.jsx("div", { className: "circuit-dots absolute inset-0 opacity-10", "aria-hidden": true }),
          e.jsx("img", {
            src: HERO_PHOTO,
            alt: "",
            className: "page-hero-photo",
            loading: "eager",
            decoding: "async",
            "aria-hidden": true,
          }),
          e.jsxs("div", {
            className: "page-hero-content relative z-10 max-w-3xl",
            children: [
              label ? e.jsx("span", { className: "badge-tech", children: label }) : null,
              e.jsx("h1", {
                className: "text-tech-primary font-display mt-3 text-3xl font-semibold tracking-tight sm:text-4xl",
                children: title,
              }),
              description
                ? e.jsx("p", {
                    className: "text-tech-muted mt-3 max-w-2xl text-sm leading-relaxed",
                    children: description,
                  })
                : null,
              children
                ? e.jsx("div", { className: "mt-5 flex flex-wrap gap-3", children })
                : null,
            ],
          }),
        ],
      }),
    }),
  });
}

export { PageHeader as P };
