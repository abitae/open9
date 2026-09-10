import { k as useSite, i as useCart, r as React, w as fetchProducts, j as jsx, a as Link, x as ShoppingBag, X as CloseIcon, A as ArrowIcon } from "./index-BHPdvlM3.js";
import { L as LoadingGrid, E as EmptyState } from "./LoadingGrid-Crwh7inI.js";
import { a as formatUsd, b as formatPen } from "./money-Ci_YdgH6.js";
import { h as fallbackImage } from "./hero-flowers-CZ7raNwX.js";
import { S as SearchIcon } from "./search-kcgCurcT.js";
import { S as StarIcon } from "./star-CKnUSSGZ.js";
import { M as MinusIcon } from "./minus-qEjTqhrN.js";
import { P as PlusIcon } from "./plus-lUpwE3Z4.js";

const h = jsx.jsx;
const hs = jsx.jsxs;
const Frag = jsx.Fragment;

function stockLimit(product) {
  if (product.stock == null || product.stock === "") {
    return Number.POSITIVE_INFINITY;
  }

  const value = Number(product.stock);

  return Number.isFinite(value) ? value : Number.POSITIVE_INFINITY;
}

function stockTone(product) {
  const stock = stockLimit(product);

  if (stock <= 0) {
    return "out";
  }

  if (stock <= 3) {
    return "low";
  }

  return "ok";
}

function stockLabel(product) {
  const stock = stockLimit(product);

  if (!Number.isFinite(stock)) {
    return "Disponible";
  }

  if (stock <= 0) {
    return "Sin stock";
  }

  if (stock <= 3) {
    return `Quedan ${stock}`;
  }

  return "En stock";
}

function PriceBlock({ product, size = "card" }) {
  const usdClass = size === "card" ? "text-tech-primary text-lg font-medium" : "text-tech-primary text-2xl font-medium";

  return hs("div", {
    className: size === "card" ? "mt-3 space-y-0.5" : "mt-4 space-y-1",
    children: [
      h("p", { className: usdClass, children: formatUsd(product.prices.USD) }),
      h("p", { className: "text-tech-muted font-mono text-xs", children: formatPen(product.prices.PEN) }),
    ],
  });
}

function QtyControls({ quantity, onMinus, onPlus, plusDisabled }) {
  return hs("div", {
    className: "mt-4 flex items-center justify-between",
    children: [
      h("button", {
        type: "button",
        "aria-label": "Quitar una unidad",
        onClick: onMinus,
        className: "liquid-glass flex h-9 w-9 items-center justify-center rounded-full",
        children: h(MinusIcon, { className: "h-3.5 w-3.5" }),
      }),
      h("span", { className: "font-mono text-tech-primary text-sm", children: quantity }),
      h("button", {
        type: "button",
        "aria-label": "Agregar una unidad",
        onClick: onPlus,
        disabled: plusDisabled,
        className: "liquid-glass flex h-9 w-9 items-center justify-center rounded-full disabled:opacity-40",
        children: h(PlusIcon, { className: "h-3.5 w-3.5" }),
      }),
    ],
  });
}

function ProductCard({ product, quantity, onOpen, onAdd, onMinus, onPlus }) {
  const tone = stockTone(product);
  const atMax = quantity >= stockLimit(product);
  const out = tone === "out";

  return hs("article", {
    className: "tech-card tech-card-hardware card-hover store-card flex flex-col overflow-hidden p-0",
    children: [
      hs("button", {
        type: "button",
        className: "store-media",
        onClick: () => onOpen(product),
        children: [
          h("img", { src: product.image_url ?? fallbackImage, alt: product.name }),
          product.badge
            ? h("span", { className: "badge-tech absolute top-3 left-3", children: product.badge })
            : null,
        ],
      }),
      hs("div", {
        className: "flex flex-1 flex-col p-5",
        children: [
          hs("div", {
            className: "flex items-center justify-between gap-2",
            children: [
              hs("div", {
                className: "flex min-w-0 items-center gap-2",
                children: [
                  product.brand_image_url
                    ? h("img", { src: product.brand_image_url, alt: "", className: "store-card-brand" })
                    : null,
                  h("span", {
                    className: "text-tech-subtle font-mono truncate text-[10px] uppercase tracking-widest",
                    children: product.brand || product.category || "",
                  }),
                ],
              }),
              hs("span", {
                className: `font-mono text-[10px] uppercase tracking-wide store-stock-${tone}`,
                children: stockLabel(product),
              }),
            ],
          }),
          h("button", {
            type: "button",
            className: "text-tech-primary mt-2 text-left text-base font-medium leading-snug",
            onClick: () => onOpen(product),
            children: product.name,
          }),
          h("p", { className: "text-tech-muted store-clamp mt-2 flex-1 text-sm leading-relaxed", children: product.description }),
          hs("div", {
            className: "mt-3 flex items-end justify-between gap-3",
            children: [
              h(PriceBlock, { product }),
              hs("div", {
                className: "flex shrink-0 items-center gap-0.5 pb-1",
                children: [
                  h(StarIcon, { className: "text-tech-accent h-3.5 w-3.5 fill-current" }),
                  h("span", { className: "font-mono text-tech-muted text-xs", children: product.rating }),
                ],
              }),
            ],
          }),
          out
            ? h("button", {
                type: "button",
                disabled: true,
                className: "liquid-glass text-tech-muted mt-4 w-full rounded-full py-2.5 text-xs font-medium opacity-60",
                children: "Sin stock",
              })
            : quantity === 0
              ? h("button", {
                  type: "button",
                  onClick: (event) => {
                    event.stopPropagation();
                    onAdd(product);
                  },
                  className: "liquid-glass-strong text-tech-primary mt-4 w-full rounded-full py-2.5 text-xs font-medium",
                  children: "Añadir al carrito",
                })
              : h(QtyControls, {
                  quantity,
                  onMinus: () => onMinus(product.id),
                  onPlus: () => onPlus(product),
                  plusDisabled: atMax,
                }),
        ],
      }),
    ],
  });
}

function BodyPortal({ children }) {
  if (typeof document === "undefined") {
    return null;
  }

  return {
    $$typeof: Symbol.for("react.portal"),
    key: null,
    children,
    containerInfo: document.body,
    implementation: null,
  };
}

function ProductModal({ product, detail, quantity, related, rate, onClose, onAdd, onMinus, onPlus, onOpen }) {
  const gallery = [product.image_url, ...((detail && detail.gallery) || [])].filter(Boolean);
  const uniqueGallery = [...new Set(gallery)];
  const [active, setActive] = React.useState(uniqueGallery[0] ?? fallbackImage);
  const tone = stockTone(product);
  const out = tone === "out";
  const atMax = quantity >= stockLimit(product);

  React.useEffect(() => {
    setActive(uniqueGallery[0] ?? fallbackImage);
  }, [product.id, detail]);

  React.useEffect(() => {
    const previous = document.body.style.overflow;
    document.body.style.overflow = "hidden";

    function onKey(event) {
      if (event.key === "Escape") {
        onClose();
      }
    }

    window.addEventListener("keydown", onKey);

    return () => {
      document.body.style.overflow = previous;
      window.removeEventListener("keydown", onKey);
    };
  }, []);

  return h(BodyPortal, {
    children: h("div", {
    className: "store-overlay",
    role: "presentation",
    onClick: onClose,
    children: hs("div", {
      role: "dialog",
      "aria-modal": "true",
      "aria-labelledby": "store-product-title",
      className: "liquid-glass-strong store-panel",
      onClick: (event) => event.stopPropagation(),
      children: [
        hs("div", {
          className: "store-panel-main",
          children: [
            hs("div", {
              className: "store-panel-media",
              children: [
                h("img", { src: active ?? fallbackImage, alt: product.name }),
                uniqueGallery.length > 1
                  ? h("div", {
                      className: "store-thumbs",
                      children: uniqueGallery.map((url) =>
                        h(
                          "button",
                          {
                            type: "button",
                            className: url === active ? "store-thumb store-thumb-active" : "store-thumb",
                            onClick: () => setActive(url),
                            children: h("img", { src: url, alt: "" }),
                          },
                          url,
                        ),
                      ),
                    })
                  : null,
              ],
            }),
            hs("div", {
              className: "store-panel-body",
              children: [
                hs("div", {
                  className: "flex items-start justify-between gap-3",
                  children: [
                    hs("div", {
                      children: [
                        product.brand
                          ? hs("p", {
                              className: "text-tech-subtle mb-1 flex items-center gap-2 font-mono text-[10px] uppercase tracking-widest",
                              children: [
                                product.brand_image_url
                                  ? h("img", { src: product.brand_image_url, alt: "", className: "store-card-brand" })
                                  : null,
                                product.brand,
                              ],
                            })
                          : null,
                        product.category
                          ? h("p", { className: "text-tech-subtle font-mono text-[10px] uppercase tracking-widest", children: product.category })
                          : null,
                        h("h2", { id: "store-product-title", className: "text-tech-primary mt-2 text-2xl font-semibold tracking-tight", children: product.name }),
                      ],
                    }),
                    h("button", {
                      type: "button",
                      "aria-label": "Cerrar ficha",
                      onClick: (event) => {
                        event.stopPropagation();
                        onClose();
                      },
                      className: "liquid-glass flex h-9 w-9 shrink-0 items-center justify-center rounded-full",
                      children: h(CloseIcon, { className: "h-4 w-4" }),
                    }),
                  ],
                }),
                hs("div", {
                  className: "mt-3 flex flex-wrap items-center gap-2",
                  children: [
                    product.badge ? h("span", { className: "badge-tech", children: product.badge }) : null,
                    h("span", { className: `font-mono text-[11px] uppercase tracking-wide store-stock-${tone}`, children: stockLabel(product) }),
                    hs("span", {
                      className: "text-tech-muted flex items-center gap-1 font-mono text-xs",
                      children: [
                        h(StarIcon, { className: "text-tech-accent h-3.5 w-3.5 fill-current" }),
                        String(product.rating),
                      ],
                    }),
                  ],
                }),
                h("p", { className: "text-tech-muted store-clamp mt-4 text-sm leading-relaxed", children: product.description }),
                h(PriceBlock, { product, size: "detail" }),
                rate
                  ? h("p", { className: "text-tech-subtle mt-2 text-[11px]", children: `Tipo de cambio referencial USD 1 = S/ ${rate.toFixed(2)}.` })
                  : null,
                out
                  ? h("p", { className: "text-tech-muted mt-6 text-sm", children: "Este producto no tiene unidades disponibles." })
                  : quantity === 0
                    ? h("button", {
                        type: "button",
                        onClick: () => onAdd(product),
                        className: "btn-primary mt-6 w-full justify-center",
                        children: "Añadir al carrito",
                      })
                    : h(QtyControls, {
                        quantity,
                        onMinus: () => onMinus(product.id),
                        onPlus: () => onPlus(product),
                        plusDisabled: atMax,
                      }),
                hs(Link, {
                  to: "/carrito",
                  className: "text-tech-accent mt-4 inline-flex items-center gap-1 text-sm hover:underline",
                  children: ["Ver carrito", h(ArrowIcon, { className: "h-4 w-4" })],
                }),
              ],
            }),
          ],
        }),
        related.length
          ? hs("div", {
              className: "store-related",
              children: [
                h("p", { className: "text-tech-subtle font-mono text-[10px] uppercase tracking-widest", children: "También en esta categoría" }),
                h("div", {
                  className: "store-related-grid",
                  children: related.map((item) =>
                    hs(
                      "button",
                      {
                        type: "button",
                        className: "store-related-card",
                        onClick: () => onOpen(item),
                        children: [
                          h("img", { src: item.image_url ?? fallbackImage, alt: item.name }),
                          hs("span", {
                            className: "store-related-copy",
                            children: [
                              h("strong", { className: "text-tech-primary", children: item.name }),
                              h("em", { className: "text-tech-muted font-mono", children: formatUsd(item.prices.USD) }),
                            ],
                          }),
                        ],
                      },
                      item.id,
                    ),
                  ),
                }),
              ],
            })
          : null,
      ],
    }),
  }),
  });
}

export default function StorePage() {
  const { site } = useSite();
  const { count, totals, quantityOf, addItem, increment, decrement } = useCart();
  const [products, setProducts] = React.useState([]);
  const [brands, setBrands] = React.useState([]);
  const [loading, setLoading] = React.useState(true);
  const [category, setCategory] = React.useState("Todo");
  const [brand, setBrand] = React.useState("");
  const [query, setQuery] = React.useState("");
  const [sort, setSort] = React.useState("catalogo");
  const [selected, setSelected] = React.useState(null);
  const [detail, setDetail] = React.useState(null);
  const [toast, setToast] = React.useState("");

  React.useEffect(() => {
    Promise.all([
      fetchProducts(),
      fetch("/api/product-brands", { headers: { Accept: "application/json" } }).then((response) => (
        response.ok ? response.json() : { data: [] }
      )),
    ])
      .then(([catalog, brandsPayload]) => {
        setProducts(catalog);
        setBrands(Array.isArray(brandsPayload?.data) ? brandsPayload.data : []);
      })
      .catch(() => {
        setProducts([]);
        setBrands([]);
      })
      .finally(() => setLoading(false));
  }, []);

  React.useEffect(() => {
    if (!selected) {
      setDetail(null);

      return;
    }

    let cancelled = false;

    fetch(`/api/products/${encodeURIComponent(selected.slug)}`, { headers: { Accept: "application/json" } })
      .then((response) => (response.ok ? response.json() : null))
      .then((payload) => {
        if (!cancelled) {
          setDetail(payload);
        }
      })
      .catch(() => {
        if (!cancelled) {
          setDetail(null);
        }
      });

    return () => {
      cancelled = true;
    };
  }, [selected]);

  const categories = React.useMemo(() => {
    const counts = {};

    for (const product of products) {
      if (!product.category) {
        continue;
      }

      counts[product.category] = (counts[product.category] || 0) + 1;
    }

    return ["Todo", ...Object.keys(counts)].map((name) => ({
      name,
      count: name === "Todo" ? products.length : counts[name],
    }));
  }, [products]);

  const visible = React.useMemo(() => {
    const needle = query.trim().toLowerCase();
    const filtered = products.filter((product) => {
      const matchesCategory = category === "Todo" || product.category === category;
      const matchesBrand = brand === "" || product.brand_slug === brand;
      const matchesQuery =
        needle === "" ||
        product.name.toLowerCase().includes(needle) ||
        (product.description?.toLowerCase().includes(needle) ?? false) ||
        (product.category?.toLowerCase().includes(needle) ?? false) ||
        (product.brand?.toLowerCase().includes(needle) ?? false);

      return matchesCategory && matchesBrand && matchesQuery;
    });

    const sorted = [...filtered];

    if (sort === "precio-asc") {
      sorted.sort((left, right) => left.prices.USD - right.prices.USD);
    } else if (sort === "precio-desc") {
      sorted.sort((left, right) => right.prices.USD - left.prices.USD);
    } else if (sort === "rating") {
      sorted.sort((left, right) => (right.rating || 0) - (left.rating || 0));
    }

    return sorted;
  }, [products, category, brand, query, sort]);

  const related = React.useMemo(() => {
    if (!selected) {
      return [];
    }

    return products.filter((product) => product.category === selected.category && product.id !== selected.id).slice(0, 3);
  }, [products, selected]);

  const rate = site?.store?.usd_pen_rate;

  function resetFilters() {
    setCategory("Todo");
    setBrand("");
    setQuery("");
    setSort("catalogo");
  }

  function showToast(message) {
    setToast(message);
    window.clearTimeout(showToast.timer);
    showToast.timer = window.setTimeout(() => setToast(""), 2200);
  }

  function add(product) {
    if (stockLimit(product) <= 0 || quantityOf(product.id) >= stockLimit(product)) {
      return;
    }

    addItem(product);
    showToast(`${product.name} se añadió al carrito`);
  }

  function plus(product) {
    if (quantityOf(product.id) >= stockLimit(product)) {
      return;
    }

    increment(product.id);
  }

  return hs(Frag, {
    children: [
      hs("section", {
        className: "store-shell",
        children: [
          h("div", {
            className: "store-wrap",
            children: hs("div", {
              children: [
                hs("header", {
                  className: "store-head",
                  children: [
                    hs("div", {
                      children: [
                        h("p", { className: "store-kicker text-tech-subtle", children: "Tienda OPEN9" }),
                        hs("h1", {
                          className: "store-title text-tech-primary font-display",
                          children: ["Hardware, cloud y software para ", h("em", { className: "text-tech-secondary", children: "tu infraestructura" })],
                        }),
                        h("p", {
                          className: "store-lead text-tech-muted",
                          children: "Paquetes de chatbots, auditorías, dashboards e integraciones. También hardware, cloud y licencias para tu infraestructura.",
                        }),
                      ],
                    }),
                    hs(Link, {
                      to: "/carrito",
                      className: "liquid-glass interactive-scale flex items-center gap-3 rounded-full px-5 py-3 active:scale-95",
                      children: [
                        h(ShoppingBag, { className: "text-tech-accent h-4 w-4" }),
                        hs("span", { className: "text-tech-primary text-sm font-medium", children: [count, " ", count === 1 ? "artículo" : "artículos"] }),
                        h("span", { className: "bg-tech-line h-4 w-px" }),
                        hs("div", {
                          className: "text-right",
                          children: [
                            h("span", { className: "text-tech-accent block font-mono text-sm", children: formatUsd(totals.USD) }),
                            h("span", { className: "text-tech-muted block font-mono text-[10px]", children: formatPen(totals.PEN) }),
                          ],
                        }),
                      ],
                    }),
                  ],
                }),
                hs("div", {
                  className: "liquid-glass store-toolbar",
                  children: [
                    hs("div", {
                      className: "liquid-glass relative flex items-center rounded-full border border-white/5 px-4",
                      children: [
                        h(SearchIcon, { className: "text-tech-muted h-4 w-4 shrink-0", "aria-hidden": true }),
                        h("input", {
                          type: "search",
                          value: query,
                          onChange: (event) => setQuery(event.target.value),
                          placeholder: "Buscar por nombre, marca o categoría...",
                          "aria-label": "Buscar producto",
                          className: "text-tech-primary placeholder:text-tech-subtle w-full bg-transparent px-3 py-3 text-sm focus:outline-none",
                        }),
                        query
                          ? h("button", {
                              type: "button",
                              onClick: () => setQuery(""),
                              "aria-label": "Limpiar búsqueda",
                              className: "text-tech-muted hover:text-tech-primary flex h-7 w-7 shrink-0 items-center justify-center rounded-full",
                              children: h(CloseIcon, { className: "h-4 w-4" }),
                            })
                          : null,
                      ],
                    }),
                    hs("label", {
                      className: "text-tech-muted flex items-center gap-2 px-2 text-xs",
                      children: [
                        "Ordenar",
                        hs("select", {
                          value: sort,
                          onChange: (event) => setSort(event.target.value),
                          className: "store-select text-tech-primary",
                          children: [
                            h("option", { value: "catalogo", children: "Catálogo" }),
                            h("option", { value: "precio-asc", children: "Precio: menor a mayor" }),
                            h("option", { value: "precio-desc", children: "Precio: mayor a menor" }),
                            h("option", { value: "rating", children: "Mejor valorados" }),
                          ],
                        }),
                      ],
                    }),
                  ],
                }),
                brands.length
                  ? hs("div", {
                      className: "store-brands",
                      children: [
                        h("p", { className: "store-brands-label text-tech-subtle", children: "Marcas" }),
                        h(
                          "button",
                          {
                            type: "button",
                            onClick: () => setBrand(""),
                            className: brand === "" ? "store-brand store-brand-active" : "store-brand",
                            children: "Todas",
                          },
                        ),
                        ...brands.map((item) =>
                          h(
                            "button",
                            {
                              type: "button",
                              onClick: () => setBrand(item.slug === brand ? "" : item.slug),
                              className: brand === item.slug ? "store-brand store-brand-active" : "store-brand",
                              children: [
                                item.image_url
                                  ? h("img", { src: item.image_url, alt: "", className: "store-brand-logo" })
                                  : null,
                                h("span", { children: item.name }),
                              ],
                            },
                            item.slug,
                          ),
                        ),
                      ],
                    })
                  : null,
                hs("div", {
                  className: "mb-8 flex flex-wrap items-center gap-2",
                  children: [
                    ...categories.map((item) =>
                      h(
                        "button",
                        {
                          type: "button",
                          onClick: () => setCategory(item.name),
                          className: category === item.name ? "filter-pill filter-pill-active" : "filter-pill",
                          children: `${item.name} ${item.count}`,
                        },
                        item.name,
                      ),
                    ),
                    hs("span", {
                      className: "text-tech-subtle ml-auto font-mono text-xs",
                      children: [visible.length, " ", visible.length === 1 ? "resultado" : "resultados"],
                    }),
                  ],
                }),
                rate
                  ? h("p", { className: "text-tech-subtle -mt-6 mb-6 text-xs", children: `Precios referenciales (USD 1 = S/ ${rate.toFixed(2)}). El cobro sigue la moneda de la pasarela.` })
                  : null,
                loading
                  ? h(LoadingGrid, { count: 6 })
                  : visible.length > 0
                    ? h("div", {
                        className: "store-grid",
                        children: visible.map((product) =>
                          h(
                            ProductCard,
                            {
                              product,
                              quantity: quantityOf(product.id),
                              onOpen: setSelected,
                              onAdd: add,
                              onMinus: decrement,
                              onPlus: plus,
                            },
                            product.id,
                          ),
                        ),
                      })
                    : h(EmptyState, {
                        title: "Sin resultados",
                        description: query || brand ? "No encontramos productos con ese filtro." : "Prueba otra categoría.",
                        actionLabel: "Ver todo",
                        onAction: resetFilters,
                      }),
                count > 0
                  ? hs("div", {
                      className: "liquid-glass-strong glow-tech store-cart-bar sticky bottom-6 mt-10 flex flex-col items-center justify-between gap-4 rounded-[2rem] p-6 sm:flex-row",
                      children: [
                        hs("div", {
                          children: [
                            hs("p", { className: "text-tech-primary text-sm font-medium", children: [count, " ", count === 1 ? "producto" : "productos", " en el carrito"] }),
                            h("p", { className: "text-tech-accent font-mono mt-1 text-lg", children: formatUsd(totals.USD) }),
                            h("p", { className: "text-tech-muted font-mono text-xs", children: formatPen(totals.PEN) }),
                          ],
                        }),
                        hs(Link, { to: "/carrito", className: "btn-primary", children: ["Ver carrito", h(ArrowIcon, { className: "h-4 w-4" })] }),
                      ],
                    })
                  : null,
                hs("p", {
                  className: "text-tech-subtle mt-8 text-center text-xs",
                  children: [
                    "¿Necesitas una configuración a medida? ",
                    h(Link, { to: "/servicios", className: "text-tech-accent hover:underline", children: "Habla con ventas" }),
                  ],
                }),
              ],
            }),
          }),
        ],
      }),
      selected
        ? h(ProductModal, {
            product: selected,
            detail,
            quantity: quantityOf(selected.id),
            related,
            rate,
            onClose: () => setSelected(null),
            onAdd: add,
            onMinus: decrement,
            onPlus: plus,
            onOpen: setSelected,
          })
        : null,
      toast
        ? h("div", { className: "liquid-glass-strong store-toast text-tech-primary text-sm", children: toast })
        : null,
    ],
  });
}
