class ModalComponent extends HTMLElement {
    constructor() {
        super();
        this.attachShadow({
            mode: "open",
        });

        this.addEventListener("closeModal", () => {
            this.remove();
        });
    }

    async connectedCallback() {
        const template = await fetch("/front/components/modal/modal.html");
        const templateToString = await template.text();
        this.shadowRoot.innerHTML = templateToString;

        this.buildForm();
    }

    buildForm() {
        const modal = this.shadowRoot.getElementById("modal");
        const h1 = document.createElement("h1");
        h1.innerHTML = this.title;

        const form = document.createElement("app-form");
        form.type = this.type;

        if (this.data) {
            form.data = this.data;
        }

        modal.appendChild(h1);
        modal.appendChild(document.createElement("hr"));
        modal.appendChild(form);
        modal.appendChild(form);
    }
}

customElements.define("app-modal", ModalComponent);
