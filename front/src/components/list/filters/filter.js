class FiltersComponent extends HTMLElement {
    constructor() {
        super();
        this.attachShadow({
            mode: "open",
        });
    }

    async connectedCallback() {
        const template = await fetch(
            `/src/components/list/filters/${
                this.type == "PRODUCT" ? "filter-products" : "filter-orders"
            }.html`
        );
        const templateToString = await template.text();
        this.shadowRoot.innerHTML = templateToString;

        this.shadowRoot
            .getElementById("search-button")
            .addEventListener("click", (e) => {
                e.preventDefault();

                document.querySelector("app-products").dispatchEvent(
                    new CustomEvent("updateListProducts", {
                        bubbles: true,
                        composed: true,
                    })
                );
            });
    }
}

customElements.define("app-filters", FiltersComponent);
