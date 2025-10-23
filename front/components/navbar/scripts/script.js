const menuList = [
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

function handleEvent(e, option) {
    window.location.assign(option.route);
}

function initNavBarMenu(componentsRoot) {
    document.getElementById("content-menu").append(
        ...menuList.map((option) => {
            const li = document.createElement("li");
            li.textContent = option.description;
            li.addEventListener("click", (e) => handleEvent(e, option));
            return li;
        })
    );
}

class NavbarComponent extends HTMLElement {
    connectedCallback() {
        fetch("/front/components/navbar/navbar.html")
            .then((res) => res.text())
            .then((html) => {
                const template = document.createElement("template");
                template.innerHTML = html;
                const clone = template.content.cloneNode(true);
                this.appendChild(clone);

                initNavBarMenu(this);
            });
    }
}

customElements.define("app-navbar", NavbarComponent);
