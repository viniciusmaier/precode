import { ProductServicesApi } from "../api/products-api.js";

export class ProductEventListeners {
    api = new ProductServicesApi();

    constructor() {
        console.log("Listenner de produtos ativo");
        document.addEventListener("saveProducts", async (data) => {
            const result = await this.api.save(data.detail);

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

            if (result.status == "sucesso") {
                const form = document
                    .querySelector("app-products")
                    .shadowRoot.querySelector("app-modal");

                if (form) {
                    form.dispatchEvent(
                        new CustomEvent("closeModal", {
                            bubbles: true,
                            composed: true,
                        })
                    );
                }
            }
        });
    }
}

new ProductEventListeners();
