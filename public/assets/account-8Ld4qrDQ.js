import{c as s,l as t}from"./index-BHPdvlM3.js";/**
 * @license lucide-react v0.546.0 - ISC
 *
 * This source code is licensed under the ISC license.
 * See the LICENSE file in the root directory of this source tree.
 */const n=[["path",{d:"M21.174 6.812a1 1 0 0 0-3.986-3.987L3.842 16.174a2 2 0 0 0-.5.83l-1.321 4.352a.5.5 0 0 0 .623.622l4.353-1.32a2 2 0 0 0 .83-.497z",key:"1a8usu"}],["path",{d:"m15 5 4 4",key:"1mk7zo"}]],r=s("pencil",n);function d(e){return t("/api/account/profile",{method:"PUT",body:JSON.stringify(e)})}function o(){return t("/api/account/addresses")}function u(e){return t("/api/account/addresses",{method:"POST",body:JSON.stringify(e)})}function i(e,a){return t(`/api/account/addresses/${e}`,{method:"PUT",body:JSON.stringify(a)})}function f(e){return t(`/api/account/addresses/${e}`,{method:"DELETE"})}function p(e){return t(`/api/account/addresses/${e}/default`,{method:"POST"})}function h(e=1){return t(`/api/account/orders?page=${e}`)}function l(e){return t(`/api/account/orders/${encodeURIComponent(e)}`)}export{r as P,l as a,o as b,i as c,u as d,f as e,h as f,p as s,d as u};
