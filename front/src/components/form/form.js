import { ProductServicesApi } from "/src/api/products-api.js";

class FormComponent extends HTMLElement {
    typeModel = {
        PRODUCT: this.toProduct,
        ORDER: this.toOrders,
    };

    constructor() {
        super();
        this.attachShadow({ mode: "open" });
    }

    async connectedCallback() {
        if (!this.type) return;
        const handler = this.typeModel[this.type.toUpperCase()];
        if (handler) await handler.call(this);
    }

    async toProduct() {
        const template = await fetch("/src/components/form/form-products.html");
        const templateToString = await template.text();
        this.shadowRoot.innerHTML = templateToString;

        if (this.data?.product) {
            const form = this.shadowRoot.querySelector("#form-container");
            const product = this.data.product;

            for (const [key, value] of Object.entries(product)) {
                const input = form.querySelector(`[name="${key}"]`);
                if (input && value != null) input.value = value;
            }

            if (product.variations) {
                const variations = JSON.parse(product.variations);
                const container = form.querySelector(".variation-list");
                const templateVariation = container.querySelector(".variation");

                container.innerHTML = "";

                variations.forEach((variation) => {
                    const clone = templateVariation.cloneNode(true);

                    for (const [key, value] of Object.entries(variation)) {
                        if (key === "specifications") continue;
                        const input = clone.querySelector(`[name="${key}"]`);
                        if (input && value != null) input.value = value;
                    }

                    if (variation.specifications?.length) {
                        const specContainer = clone.querySelector(
                            ".specification-list"
                        );
                        const specTemplate = clone.querySelector(
                            ".specification-template"
                        );

                        variation.specifications.forEach((spec) => {
                            const specClone =
                                specTemplate.content.cloneNode(true);
                            const specEl =
                                specClone.querySelector(".specification");
                            specEl.querySelector('[name="key"]').value =
                                spec.key || "";
                            specEl.querySelector('[name="value"]').value =
                                spec.value || "";
                            specContainer.appendChild(specClone);
                        });
                    }

                    container.appendChild(clone);
                });
            }
        }

        const save = async () => {
            const validateProductData = (data) => {
                const errors = [];

                if (!data.name) errors.push("Nome é obrigatório.");
                if (data.price < 0) errors.push("Preço não pode ser negativo.");
                if (data.cost < 0) errors.push("Custo não pode ser negativo.");
                if (
                    data.promotional_price &&
                    data.promotional_price > data.price
                )
                    errors.push(
                        "Preço promocional não pode ser maior que o preço normal."
                    );

                if (!data.variations?.length)
                    errors.push("Adicione ao menos uma variação.");

                data.variations.forEach((v, i) => {
                    if (!v.qty || v.qty <= 0)
                        errors.push(`Variação ${i + 1}: quantidade inválida.`);
                });

                return errors;
            };

            const form = this.shadowRoot.querySelector("#form-container");
            const formData = new FormData(form);
            const data = Object.fromEntries(formData.entries());

            for (const key in data) {
                const input = form.querySelector(`[name="${key}"]`);
                if (input?.type === "number") data[key] = Number(data[key]);
            }

            data.variations = [];
            const variationList =
                this.shadowRoot.querySelector(".variation-list");

            variationList
                .querySelectorAll(".variation")
                .forEach((variationEl) => {
                    const variationObj = {};
                    variationEl
                        .querySelectorAll("input[name]")
                        .forEach((input) => {
                            let value = input.value;
                            if (input.type === "number") value = Number(value);
                            variationObj[input.name] = value;
                        });

                    variationObj.specifications = [];
                    variationEl
                        .querySelectorAll(".specification")
                        .forEach((specEl) => {
                            const specObj = {};
                            specEl
                                .querySelectorAll("input[name]")
                                .forEach((input) => {
                                    specObj[
                                        input.name == "variationSku"
                                            ? "sku"
                                            : input.name
                                    ] = input.value;
                                });
                            variationObj.specifications.push(specObj);
                        });

                    data.variations.push(variationObj);
                });

            const errors = validateProductData(data);

            if (errors.length > 0) {
                this.showValidationErrors(errors);
                return;
            }

            this.dispatchEvent(
                new CustomEvent("saveProducts", {
                    bubbles: true,
                    composed: true,
                    detail: data,
                })
            );
        };

        const addVariation = (e) => {
            e.preventDefault();
            const containerVariation =
                this.shadowRoot.querySelector(".variation-list");
            const clone = containerVariation.children[0].cloneNode(true);

            clone
                .querySelectorAll("input")
                .forEach((input) => (input.value = ""));

            const specList = clone.querySelector(".specification-list");
            if (specList) specList.innerHTML = "";

            containerVariation.appendChild(clone);
        };

        this.createButtons({ save, addVariation });
    }

    async toOrders() {
        const template = await fetch("/src/components/form/form-orders.html");
        const templateToString = await template.text();
        this.shadowRoot.innerHTML = templateToString;

        if (this.data?.order) {
            const order = this.data.order;
            const cliente = order.cliente;

            const mapCliente = {
                cpfCnpj: cliente.cpf_cnpj,
                nomeRazao: cliente.nome_razao,
                fantasia: cliente.fantasia || "",
                email: cliente.email,
                cep: cliente.cep || "",
                endereco: cliente.endereco || "",
                numero: cliente.numero || "",
                bairro: cliente.bairro || "",
                complemento: cliente.complemento || "",
                cidade: cliente.cidade,
                uf: cliente.uf,
                responsavelRecebimento: cliente.responsavel_recebimento || "",
                residencial: cliente.residencial || "0",
                comercial: cliente.comercial || "0",
                celular: cliente.celular || "0",
            };

            Object.keys(mapCliente).forEach((key) => {
                const input = this.shadowRoot.querySelector(
                    `input[name="${key}"]`
                );
                if (input) input.value = mapCliente[key];
            });

            const mapPedido = {
                valor: order.valor || "0",
                ecommerce_id: order.ecommerce_id || "",
                pedido_id: order.pedido_id || "",
                quantidadeParcelas: order.quantidade_parcelas || "0",
                meioPagamento: order.meio_pagamento || "0",
            };

            Object.keys(mapPedido).forEach((key) => {
                const input = this.shadowRoot.querySelector(
                    `input[name="${key}"]`
                );
                if (input) input.value = mapPedido[key];
            });

            const containerItens = this.shadowRoot.querySelector(".itens-list");
            containerItens.innerHTML = "";

            order.produtos.forEach((produto) => {
                const itemDiv = document.createElement("div");
                itemDiv.classList.add("item");
                itemDiv.innerHTML = `
                        <h3>Produtos</h3>
                        <label>Sku: <input type="number" name="sku" value="${
                            produto.sku
                        }" /></label>
                        <label>Valor Unitário: <input type="number" name="valorUnitario" disabled value="${
                            produto.preco * produto.quantidade
                        }" /></label>
                        <label>Quantidade: <input type="text" name="quantidade" value="${
                            produto.quantidade
                        }" /></label>
                        <button class="remove-itens-button">Remover</button>
            `;
                containerItens.appendChild(itemDiv);
            });
        }

        const efetivarPedido = () => {
            const form = this.shadowRoot.getElementById("form-container");
            const pedido_id = form.querySelector(
                'input[name="pedido_id"]'
            ).value;
            const ecommerce_id = form.querySelector(
                'input[name="ecommerce_id"]'
            ).value;

            const data = {
                pedido_id,
                ecommerce_id,
            };

            this.dispatchEvent(
                new CustomEvent("effectuateOrder", {
                    bubbles: true,
                    composed: true,
                    detail: data,
                })
            );
        };

        const cancelarPedido = () => {
            const form = this.shadowRoot.getElementById("form-container");
            const pedido_id = form.querySelector(
                'input[name="pedido_id"]'
            ).value;
            const ecommerce_id = form.querySelector(
                'input[name="ecommerce_id"]'
            ).value;

            const data = {
                pedido_id,
                ecommerce_id,
            };

            this.dispatchEvent(
                new CustomEvent("cancelOrder", {
                    bubbles: true,
                    composed: true,
                    detail: data,
                })
            );
        };

        const save = async () => {
            const validateProductData = (data) => {
                const errors = [];

                if (!data.cpfCnpj)
                    errors.push("O CPF/CNPJ do cliente é obrigatório.");
                if (!data.nomeRazao)
                    errors.push("O nome ou razão social é obrigatório.");
                if (!data.email)
                    errors.push("O e-mail do cliente é obrigatório.");
                if (!data.produtos || data.produtos.length === 0) {
                    errors.push("Adicione ao menos um produto ao pedido.");
                } else {
                    data.produtos.forEach((p, index) => {
                        if (!p.sku || p.sku == "0")
                            errors.push(
                                `O produto ${index + 1} deve ter um SKU válido.`
                            );
                        if (
                            !p.quantidade ||
                            isNaN(p.quantidade) ||
                            p.quantidade <= 0
                        )
                            errors.push(
                                `A quantidade do produto ${
                                    index + 1
                                } deve ser maior que zero.`
                            );
                    });
                }

                if (!data.cep) errors.push("O CEP é obrigatório.");
                if (!data.endereco) errors.push("O endereço é obrigatório.");
                if (!data.numero)
                    errors.push("O número do endereço é obrigatório.");
                if (!data.cidade) errors.push("A cidade é obrigatória.");
                if (!data.uf) errors.push("O estado (UF) é obrigatório.");
                if (!data.responsavelRecebimento)
                    errors.push(
                        "O responsável pelo recebimento deve ser informado."
                    );

                if (!data.valor || data.valor <= 0)
                    errors.push(
                        "O valor total do pedido deve ser maior que zero."
                    );
                if (!data.quantidadeParcelas || data.quantidadeParcelas <= 0)
                    errors.push("A quantidade de parcelas deve ser informada.");
                if (!data.meioPagamento)
                    errors.push("O meio de pagamento deve ser informado.");

                return errors;
            };

            const form = this.shadowRoot.getElementById("form-container");
            const formData = new FormData(form);
            const data = {};

            for (const [key, value] of formData.entries()) {
                data[key] = value.trim();
            }

            const produtos = [...form.querySelectorAll(".item")].map((item) => {
                const produto = {};
                item.querySelectorAll("input").forEach((input) => {
                    produto[input.name] = input.value.trim();
                });
                return produto;
            });

            data.produtos = produtos;
            const errors = validateProductData(data);

            if (errors.length > 0) {
                this.showValidationErrors(errors);
                return;
            }

            this.dispatchEvent(
                new CustomEvent("saveOrders", {
                    bubbles: true,
                    composed: true,
                    detail: data,
                })
            );
        };

        const addProducts = () => {
            const containerProducts =
                this.shadowRoot.querySelector(".itens-list");
            const clone = containerProducts.children[0].cloneNode(true);

            clone
                .querySelectorAll("input")
                .forEach((input) => (input.value = ""));

            containerProducts.appendChild(clone);
        };

        this.createButtons({
            save,
            addProducts,
            efetivarPedido,
            cancelarPedido,
        });
        this.createInputListeners();
    }

    createInputListeners() {
        const attachItemListeners = (item) => {
            const updateItem = async () => {
                const api = new ProductServicesApi();
                const sku = skuInput.value.trim();
                const quantidade = parseInt(quantidadeInput.value) || 0;
                const pagamento = this.shadowRoot.querySelector("#pagamentos");
                const valorPagamento = pagamento.querySelector(
                    'input[name="valor"]'
                );

                if (sku.length > 0) {
                    try {
                        const res = await api.getByVariation(sku);
                        if (res.error) return;
                        const productData = res.product;
                        if (!quantidade) quantidadeInput.value = 1;
                        if (productData) {
                            valorInput.value =
                                productData.price * quantidadeInput.value || "";
                            valorPagamento.value = 0;
                            valorPagamento.value = +valorInput.value;
                        }
                    } catch (error) {
                        console.error("Erro ao consultar SKU:", error);
                    }
                }
            };

            const skuInput = item.querySelector('input[name="sku"]');
            const quantidadeInput = item.querySelector(
                'input[name="quantidade"]'
            );
            const valorInput = item.querySelector(
                'input[name="valorUnitario"]'
            );

            skuInput.addEventListener("input", updateItem);
            quantidadeInput.addEventListener("input", updateItem);
        };

        this.shadowRoot.querySelectorAll(".item").forEach(attachItemListeners);
    }

    showValidationErrors(errors = []) {
        if (!errors.length) return;

        let container = this.shadowRoot.querySelector(".form-errors");

        if (!container) {
            container = document.createElement("div");
            container.className = "form-errors";
            const form = this.shadowRoot.querySelector("form");
            form?.prepend(container);
        }

        container.innerHTML = "";

        errors.forEach((err) => {
            const errorItem = document.createElement("div");
            errorItem.className = "error-item";
            errorItem.textContent = `⚠️ ${err}`;
            container.appendChild(errorItem);
        });

        Object.assign(container.style, {
            position: "fixed",
            right: "30px",
            top: "30px",
            color: "#e74c3c",
            backgroundColor: "#fdecea",
            border: "1px solid #f5c2c7",
            padding: "8px 12px",
            borderRadius: "6px",
            marginBottom: "12px",
            fontSize: "0.9em",
            animation: "fadeIn 0.3s ease",
        });
    }

    createButtons(actions) {
        const containerButtons =
            this.shadowRoot.querySelector(".container-button");
        if (!containerButtons) return;

        const cancelButton = document.createElement("button");
        const saveButton = document.createElement("button");

        cancelButton.className = "cancelButton";
        cancelButton.textContent = "Cancelar";
        cancelButton.type = "button";

        saveButton.className = "saveButton";
        saveButton.textContent = this.data ? "Editar" : "Salvar";
        saveButton.type = "button";

        containerButtons.append(cancelButton, saveButton);
        this.handlerButtonWithAction(actions);
    }

    handlerButtonWithAction(actions) {
        if (actions.addProducts) {
            const addProductsButton =
                this.shadowRoot.querySelector(".add-itens-button");
            addProductsButton.addEventListener(
                "click",
                (e) => (e.preventDefault(), actions.addProducts())
            );

            const productList = this.shadowRoot.querySelector(".itens-list");

            productList.addEventListener("click", (e) => {
                e.preventDefault();

                if (
                    e.target.classList.contains("remove-itens-button") &&
                    productList.children.length > 1
                ) {
                    const item = e.target.closest(".item");
                    if (item) item.remove();
                }
            });
        }

        if (actions.addVariation) {
            const addVariationButton = this.shadowRoot.querySelector(
                ".add-variations-button"
            );
            addVariationButton.addEventListener("click", actions.addVariation);

            const variationList =
                this.shadowRoot.querySelector(".variation-list");

            variationList.addEventListener("click", (e) => {
                e.preventDefault();
                if (e.target.classList.contains("add-specification-button")) {
                    const variation = e.target.closest(".variation");
                    if (!variation) return;

                    const specList = variation.querySelector(
                        ".specification-list"
                    );
                    if (!specList) return;

                    const template = this.shadowRoot.querySelector(
                        ".specification-template"
                    );
                    if (!template) return;

                    const clone = template.content.cloneNode(true);
                    specList.appendChild(clone);
                }

                if (
                    e.target.classList.contains("remove-specification-button")
                ) {
                    const spec = e.target.closest(".specification");
                    if (spec) spec.remove();
                }
            });
        }

        if (actions.efetivarPedido) {
            const button = this.shadowRoot.querySelector("#btn-confirmar");
            button.addEventListener(
                "click",
                (e) => (e.preventDefault(), actions.efetivarPedido())
            );
        }

        if (actions.cancelarPedido) {
            const button = this.shadowRoot.querySelector("#btn-cancelar");
            button.addEventListener(
                "click",
                (e) => (e.preventDefault(), actions.cancelarPedido())
            );
        }

        this.shadowRoot
            .querySelector(".saveButton")
            .addEventListener(
                "click",
                (e) => (e.preventDefault(), actions.save())
            );

        this.shadowRoot.querySelector(".cancelButton")?.addEventListener(
            "click",
            (e) => (
                e.preventDefault(),
                this.dispatchEvent(
                    new CustomEvent("closeModal", {
                        bubbles: true,
                        composed: true,
                    })
                )
            )
        );
    }
}

customElements.define("app-form", FormComponent);
