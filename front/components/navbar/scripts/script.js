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
            isActived: false,
            route: "/front/pages/products/list.html",
        },
        {
            description: "Pedidos",
            isActived: false,
            route: "/front/pages/orders/list.html",
        },
    ];

    async connectedCallback() {
        const archiveHtml = await fetch("/front/components/navbar/navbar.html");
        const cssFile = await fetch("/front/components/navbar/index.css");
        const html = await archiveHtml.text();
        const css = await cssFile.text();

        this.shadowRoot.innerHTML;

        const template = document.createElement("template");
        template.innerHTML = `
        <style>${css}</style>
        ${html}
        `;

        this.shadowRoot.appendChild(template.content.cloneNode(true));
        this.initNavBarMenu();
    }

    handleEvent(e, option) {
        window.location.assign(option.route);
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
