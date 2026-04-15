import "./bootstrap";

// Import modular components
import "./components/map";
import "./components/sidebar";

function createSessionExpiredDialog() {
    const existingDialog = document.getElementById("session-expired-dialog");
    if (existingDialog) {
        return existingDialog;
    }

    const dialog = document.createElement("div");
    dialog.id = "session-expired-dialog";
    dialog.className =
        "fixed inset-0 z-[10000] hidden items-center justify-center bg-black/50 px-4 backdrop-blur-sm";
    dialog.innerHTML = `
        <div
            class="w-full max-w-md rounded-2xl border-4 border-secondary bg-white p-6 text-primary shadow-2xl"
            role="alertdialog"
            aria-modal="true"
            aria-labelledby="session-expired-title"
            aria-describedby="session-expired-description"
        >
            <div class="mb-4 flex items-start justify-between gap-4">
                <div>
                    <h2 id="session-expired-title" class="text-xl font-bold">Sessão expirada</h2>
                    <p id="session-expired-description" class="mt-2 text-sm leading-relaxed text-primary/80">
                        Sua sessão expirou por inatividade. Atualize a página para continuar usando os filtros e ações do mapa.
                    </p>
                </div>
                <button
                    type="button"
                    class="session-expired-close inline-flex h-10 w-10 items-center justify-center rounded-full bg-primary/10 text-primary transition-colors duration-200 hover:bg-primary/20 focus-visible:outline-none focus-visible:ring-4 focus-visible:ring-primary focus-visible:ring-offset-2"
                    aria-label="Fechar aviso de sessão expirada"
                >
                    <span aria-hidden="true" class="text-xl leading-none">&times;</span>
                </button>
            </div>
            <div class="flex justify-end gap-3">
                <button
                    type="button"
                    class="session-expired-refresh inline-flex items-center justify-center rounded-lg bg-secondary px-4 py-2 font-semibold text-primary transition-colors duration-200 hover:bg-secondary/90 focus-visible:outline-none focus-visible:ring-4 focus-visible:ring-primary focus-visible:ring-offset-2"
                >
                    Atualizar página
                </button>
            </div>
        </div>
    `;

    const closeDialog = () => {
        dialog.classList.add("hidden");
        dialog.classList.remove("flex");
    };

    dialog.querySelector(".session-expired-close")?.addEventListener("click", closeDialog);
    dialog.querySelector(".session-expired-refresh")?.addEventListener("click", () => {
        window.location.reload();
    });

    dialog.addEventListener("click", (event) => {
        if (event.target === dialog) {
            closeDialog();
        }
    });

    document.addEventListener("keydown", (event) => {
        if (event.key === "Escape" && !dialog.classList.contains("hidden")) {
            closeDialog();
        }
    });

    document.body.appendChild(dialog);

    return dialog;
}

function showSessionExpiredDialog() {
    const dialog = createSessionExpiredDialog();

    dialog.classList.remove("hidden");
    dialog.classList.add("flex");

    requestAnimationFrame(() => {
        dialog.querySelector(".session-expired-refresh")?.focus();
    });
}

function setupLivewireSessionExpiryHandling() {
    document.addEventListener("livewire:init", () => {
        Livewire.hook("request", ({ fail }) => {
            fail(({ status, preventDefault }) => {
                if (status !== 419) {
                    return;
                }

                preventDefault();
                showSessionExpiredDialog();
            });
        });
    });
}

document.addEventListener("DOMContentLoaded", () => {
    setupLivewireSessionExpiryHandling();
});
