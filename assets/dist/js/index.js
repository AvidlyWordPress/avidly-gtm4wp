(()=>{function t(t,e){var r="undefined"!=typeof Symbol&&t[Symbol.iterator]||t["@@iterator"];if(!r){if(Array.isArray(t)||(r=function(t,e){if(!t)return;if("string"==typeof t)return n(t,e);var r=Object.prototype.toString.call(t).slice(8,-1);"Object"===r&&t.constructor&&(r=t.constructor.name);if("Map"===r||"Set"===r)return Array.from(t);if("Arguments"===r||/^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(r))return n(t,e)}(t))||e&&t&&"number"==typeof t.length){r&&(t=r);var o=0,i=function(){};return{s:i,n:function(){return o>=t.length?{done:!0}:{done:!1,value:t[o++]}},e:function(t){throw t},f:i}}throw new TypeError("Invalid attempt to iterate non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method.")}var a,c=!0,u=!1;return{s:function(){r=r.call(t)},n:function(){var t=r.next();return c=t.done,t},e:function(t){u=!0,a=t},f:function(){try{c||null==r.return||r.return()}finally{if(u)throw a}}}}function n(t,n){(null==n||n>t.length)&&(n=t.length);for(var e=0,r=new Array(n);e<n;e++)r[e]=t[e];return r}document.addEventListener("DOMContentLoaded",(function(n){!function(){var n=document.querySelectorAll("[data-click-event]");if(n){var o,a=t(n);try{for(a.s();!(o=a.n()).done;)i=o.value,i.addEventListener("click",(function(t){var n=["HEADER","FOOTER","ASIDE","SECTION"],o=r(e(this),n);dataLayer.push({event:"agtm4wp_click",wp_click_type:this.getAttribute("data-click-type"),wp_click_event:this.getAttribute("data-click-event"),wp_click_dom:o})}))}catch(t){a.e(t)}finally{a.f()}}}()}));var e=function(t){for(var n=[];t;)null!==(t=t.parentNode)&&n.unshift(t.nodeName);return n};function r(t,n){var e="";return n.forEach((function(n){t.includes(n)&&(e=n)})),e}})();