import { OrdersServicesApi } from "../api/orders-api.js";

export class OrderEventListeners {
    api = new OrdersServicesApi();

    constructor() {
        console.log("Listenner de pedidos ativo");
        document.addEventListener("saveOrders", async (data) => {
            const result = await this.api.save(data.detail);

            document.querySelector("app-orders").dispatchEvent(
                new CustomEvent("updateListOrders", {
                    bubbles: true,
                    composed: true,
                })
            );

            document.querySelector("app-orders").dispatchEvent(
                new CustomEvent("notifyEvent", {
                    bubbles: true,
                    composed: true,
                    detail: {
                        message: result.message,
                        status: result.status,
                    },
                })
            );

            const form = document
                .querySelector("app-orders")
                .shadowRoot.querySelector("app-modal");

            if (form) {
                form.dispatchEvent(
                    new CustomEvent("closeModal", {
                        bubbles: true,
                        composed: true,
                    })
                );
            }
        });
    }
}

new OrderEventListeners();
