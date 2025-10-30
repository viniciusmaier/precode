class NavbarComponent extends HTMLElement {
    constructor() {
        super();
        this.attachShadow({
            mode: "open",
        });
    }

    menuList = [
        {
            description: "Produtos",
            page: "app-products",
        },
        {
            description: "Pedidos",
            page: "app-orders",
        },
    ];

    async connectedCallback() {
        const template = await fetch("./components/navbar/navbar.html");
        const templateToString = await template.text();

        const templateTag = document.createElement("template");
        this.shadowRoot.innerHTML = `
            ${templateToString}
        `;

        this.shadowRoot.appendChild(templateTag.content.cloneNode(true));
        this.initNavBarMenu();
    }

    handleEvent(e, option) {
        const page = document.createElement(option.page);
        const root = document.getElementById("root");
        page.style.width = "100%";

        if (root.children[1]) root.removeChild(root.children[1]);
        root.append(page);
    }

    initNavBarMenu() {
        const menu = this.shadowRoot.getElementById("content-menu");

        menu.append(
            ...this.menuList.map((option) => {
                const li = document.createElement("li");
                li.textContent = option.description;
                li.addEventListener("click", (e) =>
                    this.handleEvent(e, option)
                );
                return li;
            })
        );
    }
}

customElements.define("app-navbar", NavbarComponent);
