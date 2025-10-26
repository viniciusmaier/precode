import { OrdersServicesApi } from "../../api/orders-api.js";

class OrdersListScreen extends HTMLElement {
    services = new OrdersServicesApi();
    constructor() {
        super();
        this.attachShadow({
            mode: "open",
        });
    }

    async connectedCallback() {
        const template = await fetch("/front/pages/orders/list.html");
        const templateToString = await template.text();

        this.addEventListener("updateListProducts", async () => {
            await this.handleTable();
        });

        this.addEventListener("notifyEvent", async (obj) => {
            await this.notify(obj.detail);
        });

        this.addEventListener("detailsProductModal", async (obj) => {
            const data = await this.services.getBySku(obj.detail.sku);
            this.openModal(data);
        });

        this.shadowRoot.innerHTML = templateToString;

        this.shadowRoot
            .getElementById("register-button")
            .addEventListener("click", () => {
                this.openModal();
            });

        this.handleFilters();
        await this.handleTable();
    }

    notify(obj) {
        const container = this.shadowRoot.getElementById("notify");

        const messageDiv = document.createElement("div");
        messageDiv.innerHTML = obj.message;
        messageDiv.style.backgroundColor =
            obj.status === "sucesso" ? "#4caf50" : "#e74c3c";
        messageDiv.style.color = "#fff";
        messageDiv.style.padding = "10px";
        messageDiv.style.marginBottom = "5px";
        messageDiv.style.borderRadius = "5px";
        messageDiv.style.position = "relative";

        container.appendChild(messageDiv);

        setTimeout(() => {
            container.removeChild(messageDiv);
        }, 3000);
    }

    handleFilters() {
        const filter = document.createElement("app-filters");
        this.shadowRoot.getElementById("container-filters").appendChild(filter);
    }

    async handleTable() {
        const { data, columns } = await this.loadData();
        const container = this.shadowRoot.getElementById("table");

        const existingTable = container.querySelector("app-list");
        if (existingTable) {
            container.removeChild(existingTable);
        }

        const table = document.createElement("app-list");
        table.data = data;
        table.columns = columns;

        container.appendChild(table);
    }

    openModal(data) {
        let modal;
        if (!data) {
            modal = document.createElement("app-modal");
            modal.title = "Novo pedido de venda";
            modal.type = "ORDER";
        } else {
            modal = document.createElement("app-modal");
            modal.title = "Detalhes do Produto";
            modal.type = "PRODUCT";
            modal.data = data;
        }

        this.shadowRoot.appendChild(modal);
    }

    async loadData() {
        const filters = this.shadowRoot.querySelector("app-filters");
        const inputs = filters.shadowRoot.querySelectorAll("input");

        const filterValues = {};
        inputs.forEach((input) => {
            if (input.name) {
                filterValues[input.name] = input.value;
            }
        });

        const data = await this.services.listAll(filterValues);
        return {
            data: data,
            columns: [
                "Cod. Produto",
                "Descrição",
                "Status",
                "Data. Alteração",
                "Preço",
                "",
            ],
        };
    }
}

customElements.define("app-orders", OrdersListScreen);
