// ==========================================
// INICIALIZAÇÃO DO SISTEMA
// ==========================================
document.addEventListener("DOMContentLoaded", function () {
    console.log("Sistema de notificações do DeployMe inicializado.");
    
    // Carrega a primeira vez ao abrir a página
    carregarNotificacoes();
    
    // Procura novas notificações a cada 5 segundos automaticamente
    setInterval(carregarNotificacoes, 5000);
});

// ==========================================
// CARREGAR NOTIFICAÇÕES (VIA BUSCA AJAX)
// ==========================================
function carregarNotificacoes() {
    fetch("/loadNotifications")
    .then(response => response.json())
    .then(notificacoes => {
        atualizarBadge(notificacoes);
        renderizarDropdown(notificacoes);
    })
    .catch(err => console.error("Erro ao carregar notificações:", err));
}

// ==========================================
// ATUALIZAR CONTADOR VERMELHO (BADGE)
// ==========================================
function atualizarBadge(notificacoes) {
    const badge = document.getElementById("notificationBadge");
    if (!badge) return;

    // Filtra no array enviado pelo PHP apenas as que possuem lida == 0
    const naoLidas = notificacoes.filter(n => n.lida == 0).length;

    if (naoLidas > 0) {
        badge.innerText = naoLidas;
        badge.style.display = "inline-flex"; // Mostra o balãozinho
    } else {
        badge.style.display = "none"; // Esconde se não houver nenhuma
    }
}

// ==========================================
// RENDERIZAR O CONTEÚDO DO DROPDOWN
// ==========================================
function renderizarDropdown(notificacoes) {
    const lista = document.getElementById("listaNotificacoes");
    if (!lista) return;

    if (notificacoes.length === 0) {
        lista.innerHTML = `
            <div class="text-center p-3 text-muted">
                Nenhuma notificação.
            </div>
        `;
        return;
    }

    let html = "";
    notificacoes.forEach(n => {
        // bg-light adiciona o destaque cinza para notificações não lidas
        const bgClasse = n.lida == 0 ? 'bg-light' : '';
        
        // pointer-events: none faz o clique atravessar o texto e cair direto na div dona do ID
        html += `
            <div class="dropdown-item p-3 border-bottom notificacao-item ${bgClasse}" 
                 data-id="${n.id}" 
                 style="cursor: pointer; white-space: normal;">
                <div class="fw-semibold text-wrap" style="font-size: 0.9rem; pointer-events: none;">
                    ${n.mensagem}
                </div>
                <small class="text-muted d-block mt-1" style="pointer-events: none;">
                    ${n.created_at}
                </small>
            </div>
        `;
    });

    lista.innerHTML = html;
}

// ==========================================
// EXIBIR / OCULTAR DROPDOWN
// ==========================================
const btnNotificacoes = document.getElementById("btnNotificacoes");
const dropdown = document.getElementById("dropdownNotificacoes");

if (btnNotificacoes && dropdown) {
    // Abre ou fecha ao clicar exclusivamente no botão das notificações
    btnNotificacoes.addEventListener("click", function (e) {
        e.preventDefault();
        e.stopPropagation(); 
        
        const estaAberto = dropdown.style.display === "block";
        dropdown.style.display = estaAberto ? "none" : "block";
    });

    // Fecha o menu se clicar em qualquer outro sítio vago do ecrã
    document.addEventListener("click", function (e) {
        if (!dropdown.contains(e.target) && !btnNotificacoes.contains(e.target)) {
            dropdown.style.display = "none";
        }
    });
}

// ==========================================
// MARCAR COMO LIDA AO CLICAR NA NOTIFICAÇÃO
// ==========================================
document.addEventListener("click", function (e) {
    // Verifica se o clique ocorreu no item da notificação ou sub-elementos dela
    const item = e.target.closest(".notificacao-item");
    if (!item) return;

    e.preventDefault();
    e.stopPropagation();

    const notificacaoId = item.getAttribute("data-id");
    if (!notificacaoId) return;

    // FEEDBACK VISUAL IMEDIATO: Remove a cor cinza instantaneamente no clique
    item.classList.remove("bg-light");

    // Envia a requisição no formato x-www-form-urlencoded nativo para o $_POST do PHP
    fetch("/readNotification", {
        method: "POST",
        headers: {
            "Content-Type": "application/x-www-form-urlencoded"
        },
        body: "id=" + encodeURIComponent(notificacaoId)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Recarrega o AJAX para atualizar o Badge vermelho e remover o número lido
            carregarNotificacoes();
        }
    })
    .catch(err => console.error("Erro ao marcar como lida:", err));
});