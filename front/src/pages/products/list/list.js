import { ProductServicesApi } from "../../../api/products-api.js";

class ProductsListScreen extends HTMLElement {
    services = new ProductServicesApi();
    constructor() {
        super();
        this.attachShadow({
            mode: "open",
        });
    }

    async connectedCallback() {
        const template = await fetch("./pages/products/list/list.html");
        const templateToString = await template.text();

        this.addEventListener("updateListProducts", async () => {
            await this.handleTable();
        });

        this.addEventListener("notifyEvent", async (obj) => {
            await this.notify(obj.detail);
        });

        this.addEventListener("detailsProductModal", async (obj) => {
            const data = await this.services.getById(obj.detail.sku);
            this.openModal(data);
        });

        this.shadowRoot.innerHTML = templateToString;

        this.shadowRoot
            .getElementById("register-button")
            .addEventListener("click", () => {
                this.openModal("PRODUCT");
            });

        this.handleFilters();
        await this.handleTable();
    }

    notify(obj) {
        const existing = document.querySelector(".global-notify");
        if (existing) existing.remove();

        const messageDiv = document.createElement("div");
        messageDiv.classList.add("global-notify");
        messageDiv.textContent = obj.message;

        messageDiv.style.position = "fixed";
        messageDiv.style.top = "20px";
        messageDiv.style.right = "20px";
        messageDiv.style.padding = "16px 24px";
        messageDiv.style.borderRadius = "10px";
        messageDiv.style.fontFamily = "'Inter', sans-serif";
        messageDiv.style.fontSize = "15px";
        messageDiv.style.fontWeight = "500";
        messageDiv.style.color = "#fff";
        messageDiv.style.zIndex = "999999";
        messageDiv.style.boxShadow = "0 4px 12px rgba(0, 0, 0, 0.25)";
        messageDiv.style.transition = "all 0.4s ease";
        messageDiv.style.opacity = "0";
        messageDiv.style.transform = "translateY(-20px)";
        messageDiv.style.backdropFilter = "blur(6px)";
        messageDiv.style.border = "1px solid rgba(255,255,255,0.2)";
        messageDiv.style.cursor = "pointer";

        if (obj.status === "sucesso") {
            messageDiv.style.background =
                "linear-gradient(135deg, #28a745, #218838)";
        } else if (obj.status === "erro") {
            messageDiv.style.background =
                "linear-gradient(135deg, #dc3545, #b02a37)";
        } else {
            messageDiv.style.background =
                "linear-gradient(135deg, #6c757d, #495057)";
        }

        document.body.appendChild(messageDiv);

        setTimeout(() => {
            messageDiv.style.opacity = "1";
            messageDiv.style.transform = "translateY(0)";
        }, 10);

        setTimeout(() => {
            messageDiv.style.opacity = "0";
            messageDiv.style.transform = "translateY(-20px)";
            setTimeout(() => messageDiv.remove(), 400);
        }, 3000);

        messageDiv.addEventListener("click", () => {
            messageDiv.style.opacity = "0";
            messageDiv.style.transform = "translateY(-20px)";
            setTimeout(() => messageDiv.remove(), 400);
        });
    }

    handleFilters() {
        const filter = document.createElement("app-filters");
        filter.type = "PRODUCT";
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
        table.type = "PRODUCT";

        container.appendChild(table);
    }

    openModal(data) {
        let modal = document.createElement("app-modal");
        modal.type = "PRODUCT";

        if (!data) {
            modal.title = "Cadastro de Produtos";
        } else {
            modal.title = "Detalhes do Produto";
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
                "Sku",
                "Descrição",
                "Status",
                "Data. Alteração",
                "Preço",
                "",
            ],
        };
    }
}

customElements.define("app-products", ProductsListScreen);
