import { i as useCart, k as useSite, j as jsx, a as Link, A as ArrowIcon } from "./index-BHPdvlM3.js";
import { a as formatUsd, b as formatPen } from "./money-Ci_YdgH6.js";
import { h as fallbackImage } from "./hero-flowers-CZ7raNwX.js";
import { A as ArrowLeft } from "./arrow-left-B2ABVgQN.js";
import { S as ShoppingBag } from "./shopping-bag-BOLMAQa3.js";
import { S as ShieldCheck } from "./shield-check-DE3SFgDg.js";
import { M as MinusIcon } from "./minus-qEjTqhrN.js";
import { P as PlusIcon } from "./plus-lUpwE3Z4.js";
import { T as TrashIcon } from "./trash-2-ByPb2Di2.js";

const h = jsx.jsx;
const hs = jsx.jsxs;
const Frag = jsx.Fragment;

function CartItem({ item }) {
  const { increment, decrement, setQuantity, removeItem } = useCart();
  const { product, quantity } = item;
  const usd = product.prices.USD * quantity;
  const pen = product.prices.PEN * quantity;

  return hs("div", {
    className: "liquid-glass flex flex-col gap-4 rounded-3xl p-4 sm:flex-row sm:items-center",
    children: [
      h("div", {
        className: "h-20 w-20 shrink-0 overflow-hidden rounded-2xl",
        children: h("img", { src: product.image_url ?? fallbackImage, alt: product.name, className: "h-full w-full object-cover" }),
      }),
      hs("div", {
        className: "min-w-0 flex-1",
        children: [
          product.category
            ? h("span", { className: "text-tech-subtle font-mono text-[10px] uppercase tracking-widest", children: product.category })
            : null,
          h("h3", { className: "text-tech-primary truncate text-sm font-medium", children: product.name }),
          hs("div", {
            className: "mt-1 flex items-baseline gap-2",
            children: [
              h("span", { className: "text-tech-secondary text-sm", children: formatUsd(product.prices.USD) }),
              h("span", { className: "text-tech-muted font-mono text-[11px]", children: formatPen(product.prices.PEN) }),
            ],
          }),
        ],
      }),
      hs("div", {
        className: "flex items-center gap-2",
        children: [
          h("button", {
            type: "button",
            "aria-label": "Quitar una unidad",
            onClick: () => decrement(product.id),
            className: "liquid-glass flex h-8 w-8 items-center justify-center rounded-full",
            children: h(MinusIcon, { className: "h-3.5 w-3.5" }),
          }),
          h("input", {
            type: "number",
            min: 1,
            value: quantity,
            onChange: (event) => setQuantity(product.id, Math.max(1, Math.floor(Number(event.target.value) || 1))),
            "aria-label": `Cantidad de ${product.name}`,
            className: "text-tech-primary w-12 rounded-xl border border-white/10 bg-transparent py-1.5 text-center font-mono text-sm focus:border-white/30 focus:outline-none",
          }),
          h("button", {
            type: "button",
            "aria-label": "Agregar una unidad",
            onClick: () => increment(product.id),
            className: "liquid-glass flex h-8 w-8 items-center justify-center rounded-full",
            children: h(PlusIcon, { className: "h-3.5 w-3.5" }),
          }),
        ],
      }),
      hs("div", {
        className: "flex items-center justify-between gap-3 sm:w-36 sm:flex-col sm:items-end sm:justify-center",
        children: [
          hs("div", {
            className: "text-right",
            children: [
              h("p", { className: "text-tech-primary font-mono text-sm", children: formatUsd(usd) }),
              h("p", { className: "text-tech-muted font-mono text-[10px]", children: formatPen(pen) }),
            ],
          }),
          h("button", {
            type: "button",
            "aria-label": `Eliminar ${product.name}`,
            onClick: () => removeItem(product.id),
            className: "text-tech-muted hover:text-red-400 flex h-8 w-8 items-center justify-center rounded-full transition-colors",
            children: h(TrashIcon, { className: "h-4 w-4" }),
          }),
        ],
      }),
    ],
  });
}

export default function CartPage() {
  const { items, count, totals, clear } = useCart();
  const { site } = useSite();
  const rate = site?.store?.usd_pen_rate;

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
                        h("p", { className: "store-kicker text-tech-subtle", children: "Carrito" }),
                        hs("h1", {
                          className: "store-title text-tech-primary font-display",
                          children: ["Tu ", h("em", { className: "text-tech-secondary", children: "pedido" })],
                        }),
                        h("p", {
                          className: "store-lead text-tech-muted",
                          children: count
                            ? "Revisa cantidades y continúa al pago con MercadoPago."
                            : "Aún no hay productos. Explora paquetes OPEN9, hardware y cloud para tu empresa.",
                        }),
                      ],
                    }),
                    hs(Link, {
                      to: "/tienda",
                      className: "btn-ghost",
                      children: [h(ArrowLeft, { className: "h-4 w-4" }), "Seguir comprando"],
                    }),
                  ],
                }),
                count === 0
                  ? hs("div", {
                      className: "liquid-glass flex flex-col items-center justify-center rounded-3xl px-6 py-12 text-center",
                      children: [
                        h("div", {
                          className: "liquid-glass-strong mb-5 flex h-14 w-14 items-center justify-center rounded-full",
                          children: h(ShoppingBag, { className: "text-tech-accent h-6 w-6" }),
                        }),
                        h("p", { className: "text-tech-primary text-lg font-medium", children: "Tu carrito está vacío" }),
                        h("p", {
                          className: "text-tech-muted mt-2 max-w-sm text-sm leading-relaxed",
                          children: "Hardware, cloud y software listos para añadir.",
                        }),
                        hs(Link, {
                          to: "/tienda",
                          className: "btn-primary mt-6",
                          children: ["Ir a la tienda", h(ArrowIcon, { className: "h-4 w-4" })],
                        }),
                      ],
                    })
                  : hs("div", {
                      className: "grid gap-8 lg:grid-cols-[1fr_380px]",
                      children: [
                        hs("div", {
                          className: "space-y-4",
                          children: [
                            hs("div", {
                              className: "flex items-center justify-between",
                              children: [
                                hs("h2", {
                                  className: "text-tech-primary text-sm font-medium",
                                  children: [count, " ", count === 1 ? "producto" : "productos"],
                                }),
                                h("button", {
                                  type: "button",
                                  onClick: clear,
                                  className: "text-tech-muted hover:text-tech-primary text-xs transition-colors",
                                  children: "Vaciar carrito",
                                }),
                              ],
                            }),
                            ...items.map((item) => h(CartItem, { item }, item.product.id)),
                          ],
                        }),
                        h("aside", {
                          className: "lg:sticky lg:top-28 lg:self-start",
                          children: hs("div", {
                            className: "liquid-glass-strong rounded-[2rem] p-6",
                            children: [
                              h("h2", { className: "text-tech-primary font-display text-lg font-semibold", children: "Resumen del pedido" }),
                              hs("dl", {
                                className: "mt-5 space-y-3 text-sm",
                                children: [
                                  hs("div", {
                                    className: "flex items-center justify-between",
                                    children: [
                                      hs("dt", { className: "text-tech-muted", children: ["Subtotal (", count, ")"] }),
                                      h("dd", { className: "text-tech-secondary font-mono", children: formatUsd(totals.USD) }),
                                    ],
                                  }),
                                  hs("div", {
                                    className: "flex items-center justify-between",
                                    children: [
                                      h("dt", { className: "text-tech-muted", children: "Envío" }),
                                      h("dd", { className: "text-tech-subtle text-xs", children: "A coordinar" }),
                                    ],
                                  }),
                                  h("div", { className: "bg-tech-line my-2 h-px w-full" }),
                                  hs("div", {
                                    className: "flex items-baseline justify-between",
                                    children: [
                                      h("dt", { className: "text-tech-primary font-medium", children: "Total" }),
                                      hs("dd", {
                                        className: "text-right",
                                        children: [
                                          h("span", { className: "text-tech-accent block font-mono text-xl", children: formatUsd(totals.USD) }),
                                          h("span", { className: "text-tech-muted block font-mono text-xs", children: formatPen(totals.PEN) }),
                                        ],
                                      }),
                                    ],
                                  }),
                                ],
                              }),
                              rate
                                ? h("p", {
                                    className: "text-tech-subtle mt-3 text-[11px] leading-relaxed",
                                    children: `Tipo de cambio referencial USD 1 = S/ ${rate.toFixed(2)}.`,
                                  })
                                : null,
                              hs(Link, {
                                to: "/checkout",
                                className: "btn-primary mt-6 w-full justify-center",
                                children: ["Proceder al pago", h(ArrowIcon, { className: "h-4 w-4" })],
                              }),
                              hs("p", {
                                className: "text-tech-subtle mt-4 flex items-center justify-center gap-1.5 text-[11px]",
                                children: [h(ShieldCheck, { className: "h-3.5 w-3.5" }), "Pago protegido y cifrado con MercadoPago."],
                              }),
                            ],
                          }),
                        }),
                      ],
                    }),
              ],
            }),
          }),
        ],
      }),
    ],
  });
}
