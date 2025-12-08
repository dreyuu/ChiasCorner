<link rel="stylesheet" href="css/components.css">

<div id="custom-modal" class="custom-modal hidden">
    <div class="custom-modal-box">

        <div id="custom-modal-icon" class="custom-modal-icon"></div>
        <div id="custom-modal-message"></div>

        <div class="custom-modal-buttons">
            <button id="custom-ok" class="btn">OK</button>
            <button id="custom-yes" class="btn">YES</button>
            <button id="custom-no" class="btn">NO</button>
        </div>

    </div>
</div>

<div id="pageLoader">
    <div class="loader"></div>
    <div class="loading-text">Loading...</div>
</div>

<script>
    const CustomAlert = {
        modal: document.getElementById("custom-modal"),
        messageBox: document.getElementById("custom-modal-message"),
        iconBox: document.getElementById("custom-modal-icon"),
        btnOk: document.getElementById("custom-ok"),
        btnYes: document.getElementById("custom-yes"),
        btnNo: document.getElementById("custom-no"),

        // Simple alert with optional type and auto-close
        alert(message, type = "info", autoClose = false, duration = 2000) {
            return new Promise(resolve => {
                this.messageBox.textContent = message;
                this.btnOk.classList.remove("hidden");
                this.btnYes.classList.add("hidden");
                this.btnNo.classList.add("hidden");

                // Set icon
                switch (type) {
                    case "success":
                        this.iconBox.textContent = "✅";
                        break;
                    case "warning":
                        this.iconBox.textContent = "⚠️";
                        break;
                    case "error":
                        this.iconBox.textContent = "❌";
                        break;
                    default:
                        this.iconBox.textContent = "ℹ️";
                }

                this.modal.classList.remove("hidden");

                const closeModal = () => {
                    this.modal.classList.add("hidden");
                    resolve(true);
                };

                this.btnOk.onclick = closeModal;

                // Auto-close if specified
                if (autoClose) {
                    setTimeout(closeModal, duration);
                }
            });
        },

        confirm(message, type = "info") {
            return new Promise(resolve => {
                this.messageBox.textContent = message;

                this.btnOk.classList.add("hidden");
                this.btnYes.classList.remove("hidden");
                this.btnNo.classList.remove("hidden");

                // Set icon
                switch (type) {
                    case "success":
                        this.iconBox.textContent = "✅";
                        break;
                    case "warning":
                        this.iconBox.textContent = "⚠️";
                        break;
                    case "error":
                        this.iconBox.textContent = "❌";
                        break;
                    default:
                        this.iconBox.textContent = "ℹ️";
                }

                this.modal.classList.remove("hidden");

                this.btnYes.onclick = () => {
                    this.modal.classList.add("hidden");
                    resolve(true);
                };

                this.btnNo.onclick = () => {
                    this.modal.classList.add("hidden");
                    resolve(false);
                };
            });
        }
    };

    const loader = {
        show() {
            document.getElementById("pageLoader").style.visibility = "visible";
            document.getElementById("pageLoader").style.opacity = "1";
        },
        hide() {
            document.getElementById("pageLoader").style.visibility = "hidden";
            document.getElementById("pageLoader").style.opacity = "0";
        }
    }
</script>
