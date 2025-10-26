import { ProductServicesApi } from "/front/api/products-api.js";
const typeModel = {
    PRODUCT: "PRODUCT",
    ORDER: "ORDER",
};

class FormComponent extends HTMLElement {
    constructor() {
        super();
        this.attachShadow({
            mode: "open",
        });
    }

    async connectedCallback() {
        const actions =
            this.type == "PRODUCT"
                ? await this.toProduct()
                : await this.toOrders();

        if (this.data) {
            const form = this.shadowRoot.getElementById("form-container");

            for (const [key, value] of Object.entries(this.data.product)) {
                const input = form.querySelector(`[name="${key}"]`);
                if (value && input) {
                    input.value = value;
                }
            }
        }
    }

    async toProduct() {
        const template = await fetch(
            "/front/components/form/form-products.html"
        );

        const templateToString = await template.text();
        this.shadowRoot.innerHTML = `
            ${templateToString}
        `;

        async function save(product) {
            const api = new ProductServicesApi();
            const result = await api.save(product);
            return result;
        }

        this.createButtons({
            save: save(),
        });
    }

    async toOrders() {
        const template = await fetch("/front/components/form/form-orders.html");

        const templateToString = await template.text();
        this.shadowRoot.innerHTML = `
            ${templateToString}
        `;

        async function save(order) {}

        this.createButtons({
            save: save(),
        });
    }

    createButtons(actions) {
        const containerButtons =
            this.shadowRoot.querySelector(".container-button");

        const cancelButton = document.createElement("button");
        const saveButton = document.createElement("button");

        cancelButton.innerHTML = "Cancelar";
        saveButton.innerHTML = this.data ? "Editar" : "Salvar";

        containerButtons.appendChild(cancelButton);
        containerButtons.appendChild(saveButton);

        this.handlerButtonWithAction(actions);
    }

    handlerButtonWithAction(action) {
        const cancelButton = document.createElement("button");
        const saveButton = document.createElement("button");
        cancelButton.addEventListener("click", () => {
            this.dispatchEvent(
                new CustomEvent("closeModal", {
                    bubbles: true,
                    composed: true,
                })
            );
        });

        saveButton.addEventListener("click", async (e) => {
            e.preventDefault();

            const form = this.shadowRoot.getElementById("form-container");
            const formData = new FormData(form);
            const data = Object.fromEntries(formData.entries());

            for (const key in data) {
                const input = form.querySelector(`[name="${key}"]`);
                if (input.type === "number") data[key] = Number(data[key]);
            }

            const result = await action.save(data);

            document.querySelector("app-products").dispatchEvent(
                new CustomEvent("updateListProducts", {
                    bubbles: true,
                    composed: true,
                })
            );

            document.querySelector("app-products").dispatchEvent(
                new CustomEvent("notifyEvent", {
                    bubbles: true,
                    composed: true,
                    detail: {
                        message: result.message,
                        status: result.status,
                    },
                })
            );

            this.dispatchEvent(
                new CustomEvent("closeModal", {
                    bubbles: true,
                    composed: true,
                })
            );
        });
    }
}

customElements.define("app-form", FormComponent);
