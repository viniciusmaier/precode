class ListComponent extends HTMLElement {
    constructor() {
        super();
        this.attachShadow({
            mode: "open",
        });
        this._data = [];
    }

    set data(value) {
        this._data = value;
        this.renderData();
    }

    get data() {
        return this._data;
    }

    async connectedCallback() {
        const template = await fetch("./components/list/table/table-list.html");

        const templateToString = await template.text();

        this.shadowRoot.innerHTML = templateToString;

        queueMicrotask(() => {
            this.handleColumns();
            this.renderData();
        });
    }

    handleColumns() {
        const columns = this.columns || [];
        const tr = this.shadowRoot.getElementById("columns");

        columns.forEach((c) => {
            const th = document.createElement("th");
            th.innerHTML = c;
            tr.appendChild(th);
        });
    }

    renderData() {
        const table = this.shadowRoot.getElementById("data");
        if (!table) return;
        table.innerHTML = "";

        (this.type == "PRODUCT"
            ? this._data.products
            : this._data.orders
        ).forEach((obj) => {
            const tr = document.createElement("tr");

            Object.values(obj).forEach((value) => {
                const td = document.createElement("td");
                td.textContent = value;
                tr.appendChild(td);
            });

            const btn = document.createElement("button");
            btn.textContent = "Detalhes";

            btn.addEventListener("click", (e) => {
                const button = e.currentTarget;
                const tr = button.closest("tr");
                const cells = tr.querySelectorAll("td");
                const event =
                    this.type == "PRODUCT"
                        ? new CustomEvent("detailsProductModal", {
                              bubbles: true,
                              composed: true,
                              detail: {
                                  sku: cells[0].textContent,
                              },
                          })
                        : new CustomEvent("detailsOrderModal", {
                              bubbles: true,
                              composed: true,
                              detail: {
                                  orderId: cells[0].textContent,
                              },
                          });

                this.dispatchEvent(event);
            });

            tr.appendChild(btn);
            table.appendChild(tr);
        });
    }
}

customElements.define("app-list", ListComponent);
