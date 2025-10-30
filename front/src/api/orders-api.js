import { ApiServices } from "./api.js";

export class OrdersServicesApi {
    async save(order) {
        const result = await fetch(
            `${ApiServices.route}/orders/save-order.php`,
            {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                },
                body: JSON.stringify(order),
            }
        );

        const json = await result.json();
        return result.status == 200
            ? {
                  status: "sucesso",
                  message: json["sucesso"],
              }
            : {
                  status: "erro",
                  message: json[error],
              };
    }

    async effective(order) {
        const result = await fetch(
            `${ApiServices.route}/orders/effective-order.php`,
            {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                },
                body: JSON.stringify(order),
            }
        );

        const json = await result.json();
        return result.status == 200
            ? {
                  status: "sucesso",
                  message: json["sucesso"],
              }
            : {
                  status: "erro",
                  message: json[error],
              };
    }

    async cancel(order) {
        const result = await fetch(
            `${ApiServices.route}/orders/cancel-order.php`,
            {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                },
                body: JSON.stringify(order),
            }
        );

        const json = await result.json();
        return result.status == 200
            ? {
                  status: "sucesso",
                  message: json["sucesso"],
              }
            : {
                  status: "erro",
                  message: json[error],
              };
    }

    async listAll(filterValues) {
        const filters = {};

        // if (filterValues.sku) filters.sku = filterValues.sku;
        // if (filterValues.description)
        //     filters.description = filterValues.description;
        // if (filterValues.status) filters.status = filterValues.status;

        const params = new URLSearchParams(filters);

        const result = await fetch(`${ApiServices.route}/orders/list-all.php`);

        return await result.json();
    }

    async getById(orderId) {
        const result = await fetch(
            `${ApiServices.route}/orders/get-by-id.php?order_id=${orderId}`
        );
        return await result.json();
    }
}
