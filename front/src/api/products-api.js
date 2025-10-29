import { ApiServices } from "./api.js";

export class ProductServicesApi {
    async save(product) {
        const result = await fetch(
            `${ApiServices.route}/products/save-product.php`,
            {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                },
                body: JSON.stringify(product),
            }
        );

        const json = await result.json();

        return result.status == 200
            ? {
                  status: "sucesso",
                  message: json["success"],
              }
            : {
                  status: "erro",
                  message: json["erro"],
              };
    }

    async listAll(filterValues) {
        const filters = {};

        if (filterValues.id) filters.id = filterValues.id;
        if (filterValues.description)
            filters.description = filterValues.description;
        if (filterValues.status) filters.status = filterValues.status;

        const params = new URLSearchParams(filters);

        const result = await fetch(
            `${ApiServices.route}/products/list-all.php?${params}`
        );

        return await result.json();
    }

    async getById(id) {
        const result = await fetch(
            `${ApiServices.route}/products/get-by-id.php?id=${id}`
        );

        return await result.json();
    }

    async getBySku(sku) {
        const result = await fetch(
            `${ApiServices.route}/products/get-by-sku.php?sku=${sku}`
        );

        return await result.json();
    }
}
