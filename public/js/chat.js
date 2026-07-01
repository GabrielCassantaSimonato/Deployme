let usuarioEstaNoFim = true;
let mensagemSelecionada = null; // Escopo global necessário para o backend capturar depois
let menuMensagem = document.getElementById("menuMensagem");

// Novas variáveis globais de apoio ao Modal
let textoSelecionado = ""; 
let imagemSelecionada = "";

// ==========================================
// SCROLL INICIAL DO CHAT
// ==========================================
document.addEventListener("DOMContentLoaded", () => {
    const mensagens = document.getElementById("mensagens");
    if (!mensagens) return;

    mensagens.scrollTop = mensagens.scrollHeight;

    mensagens.addEventListener("scroll", () => {
        const distanciaDoFim =
            mensagens.scrollHeight -
            mensagens.scrollTop -
            mensagens.clientHeight;

        usuarioEstaNoFim = distanciaDoFim < 80;
    });
});

// Timers assíncronos
document.addEventListener("DOMContentLoaded", function () {
    if (typeof conversaAtual === 'undefined' || !conversaAtual) {
        return;
    }

    carregarMensagens();
    setInterval(carregarMensagens, 2000);
    setInterval(carregarConversas, 2000);

    // Inicializa a escuta dos inputs do chat de forma protegida
    inicializarInputsChat();
});

// ==========================================
// OPERAÇÕES ASSÍNCRONAS (AJAX FETCH)
// ==========================================
function carregarMensagens() {
    if (typeof conversaAtual === 'undefined' || !conversaAtual) return;

    fetch('/loadMessages?conversa=' + conversaAtual)
        .then(response => response.json())
        .then(dados => {
            let html = "";

            dados.forEach(mensagem => {
                const minhaMensagem = mensagem.remetente_id == usuarioLogado;
                let foto = mensagem.foto
                    ? "/uploads/fotos/" + mensagem.foto
                    : "/uploads/fotos/default-user.png";

                let minhaFoto = usuarioFoto
                    ? "/uploads/fotos/" + usuarioFoto
                    : "/uploads/fotos/default-user.png";

                html += `<div class="d-flex mb-4 ${minhaMensagem ? 'justify-content-end' : 'justify-content-start'}">`;

                if (!minhaMensagem) {
                    html += `
                        <img src="${foto}" width="40" height="40" class="rounded-circle me-2" style="object-fit:cover;">
                    `;
                }

                let conteudo = "";
                if (mensagem.mensagem) {
                    conteudo += `<div>${mensagem.mensagem.replace(/\n/g, "<br>")}</div>`;
                }

                if (mensagem.imagem) {
                    conteudo += `
                        <div class="mt-2">
                            <img src="/uploads/chat/${mensagem.imagem}" class="img-fluid rounded" style="max-width:250px; border-radius:15px; cursor:pointer;">
                        </div>
                    `;
                }

                // Injeção do data-id e data-image garantida na renderização dinâmica
                html += `
                    <div>
                        <div class="chat-bubble ${minhaMensagem ? 'chat-me' : 'chat-other'}" 
                             ${minhaMensagem ? `data-id="${mensagem.id}" data-message="${mensagem.mensagem ? mensagem.mensagem.replace(/"/g, '&quot;') : ''}" data-image="${mensagem.imagem ? mensagem.imagem : ''}"` : ''}>
                            ${conteudo}
                        </div>
                        <small class="text-muted d-block ${minhaMensagem ? 'text-end' : ''}">
                            ${formatarData(mensagem.created_at)}
                        </small>
                    </div>
                `;

                if (minhaMensagem) {
                    html += `
                        <img src="${minhaFoto}" width="40" height="40" class="rounded-circle ms-2" style="object-fit:cover;">
                    `;
                }

                html += `</div>`;
            });

            document.getElementById("mensagens").innerHTML = html;

            const mensagens = document.getElementById("mensagens");
            if (usuarioEstaNoFim) {
                mensagens.scrollTop = mensagens.scrollHeight;
            }
        });
}

function formatarData(data) {
    let d = new Date(data);
    return d.toLocaleString("pt-BR");
}

function carregarConversas() {
    fetch('/loadConversations')
        .then(response => response.json())
        .then(usuarios => {
            let html = "";

            usuarios.forEach(usuario => {
                let foto = usuario.foto ? "/uploads/fotos/" + usuario.foto : "/uploads/fotos/default-user.png";
                let ativo = usuario.id == usuarioSelecionado ? "usuario-ativo" : "";
                let ultimaMensagem = usuario.ultima_mensagem
                        ? (usuario.foi_enviada_por_mim ? "Você: " : "") + usuario.ultima_mensagem
                        : "Clique para conversar";

                let ultimaData = "Nenhuma mensagem";
                if (usuario.ultima_data) {
                    let data = new Date(usuario.ultima_data);
                    let hoje = new Date();

                    if (data.toDateString() === hoje.toDateString()) {
                        ultimaData = "Hoje • " + data.toLocaleTimeString("pt-BR", { hour: "2-digit", minute: "2-digit" });
                    } else {
                        ultimaData = data.toLocaleDateString("pt-BR") + " • " + data.toLocaleTimeString("pt-BR", { hour: "2-digit", minute: "2-digit" });
                    }
                }

                html += `
                <a href="/openConversation?usuario=${usuario.id}" class="usuario-chat text-decoration-none text-dark d-block p-3 border-bottom ${ativo}">
                    <div class="d-flex align-items-center">
                        <img src="${foto}" width="50" height="50" class="rounded-circle me-3" style="object-fit:cover;">
                        <div>
                            <div class="fw-bold d-flex align-items-center">
                                ${usuario.nome}
                                ${usuario.nao_lidas > 0 ? `<span class="badge bg-danger ms-2">${usuario.nao_lidas}</span>` : ""}
                            </div>
                            <small class="text-muted d-block">${ultimaMensagem}</small>
                            <small class="text-muted text-uppercase" style="font-size:.75rem">${ultimaData}</small>
                        </div>
                    </div>
                </a>
                `;
            });

            document.getElementById("listaUsuarios").innerHTML = html;
        });
}

// ==========================================
// INICIALIZADOR ISOLADO DOS INPUTS DO CHAT
// ==========================================
function inicializarInputsChat() {
    const botaoEmoji = document.getElementById("btnEmoji");
    const emojiContainer = document.getElementById("emojiContainer");
    const inputMensagem = document.getElementById("inputMensagem");
    const picker = document.querySelector("emoji-picker");

    const inputImagem = document.getElementById("imagem");
    const previewImagem = document.getElementById("previewImagem");
    const previewImagemChat = document.getElementById("previewImagemChat");
    const removerImagem = document.getElementById("removerImagem");
    const btnImagem = document.getElementById("btnImagem");
    const badgeImagem = document.getElementById("badgeImagem");

    // Comportamento do seletor de emojis
    if (botaoEmoji && emojiContainer) {
        botaoEmoji.addEventListener("click", (e) => {
            e.stopPropagation();
            emojiContainer.style.display = emojiContainer.style.display === "block" ? "none" : "block";
        });
    }

    if (picker && inputMensagem) {
        picker.addEventListener("emoji-click", event => {
            inputMensagem.value += event.detail.unicode;
            inputMensagem.focus();
        });
    }

    // Comportamento da pré-visualização da imagem
    if (inputImagem && previewImagem && previewImagemChat) {
        inputImagem.addEventListener("change", function () {
            const arquivo = this.files[0];
            if (!arquivo) {
                previewImagem.style.display = "none";
                previewImagemChat.src = "";
                return;
            }

            const reader = new FileReader();
            reader.onload = function (e) {
                previewImagemChat.src = e.target.result;
                previewImagem.style.display = "block";

                if (btnImagem) {
                    btnImagem.classList.remove("btn-outline-secondary");
                    btnImagem.classList.add("btn-primary");
                }
                if (badgeImagem) badgeImagem.style.display = "inline";
            };
            reader.readAsDataURL(arquivo);
        });
    }

    if (removerImagem) {
        removerImagem.addEventListener("click", function () {
            if (inputImagem) inputImagem.value = "";
            if (previewImagemChat) previewImagemChat.src = "";
            if (previewImagem) previewImagem.style.display = "none";

            if (btnImagem) {
                btnImagem.classList.remove("btn-primary");
                btnImagem.classList.add("btn-outline-secondary");
            }
            if (badgeImagem) badgeImagem.style.display = "none";
        });
    }
}

// ==========================================
// MENU DE CONTEXTO DAS MENSAGENS
// ==========================================

// Clique com botão direito
document.addEventListener("contextmenu", function (e) {
    const bubble = e.target.closest(".chat-me");

    if (!bubble) {
        return;
    }

    e.preventDefault();

    mensagemSelecionada = bubble.dataset.id;
    textoSelecionado = bubble.dataset.message || ""; 
    imagemSelecionada = bubble.dataset.image || ""; // Captura o nome da imagem associado

    console.log("Mensagem selecionada:", mensagemSelecionada, "Texto:", textoSelecionado, "Imagem:", imagemSelecionada);

    menuMensagem.style.display = "block";
    menuMensagem.style.left = e.pageX + "px";
    menuMensagem.style.top = e.pageY + "px";
});

// Fecha o menu quando clicar fora
document.addEventListener("click", function (e) {
    if (!e.target.closest("#menuMensagem")) {
        menuMensagem.style.display = "none";
    }

    // Fecha a caixa de emojis
    const emojiContainer = document.getElementById("emojiContainer");
    const botaoEmoji = document.getElementById("btnEmoji");

    if (
        emojiContainer &&
        !emojiContainer.contains(e.target) &&
        e.target !== botaoEmoji
    ) {
        emojiContainer.style.display = "none";
    }
});

// ==========================================
// EXCLUIR MENSAGEM
// ==========================================
document.getElementById("btnExcluirMensagem").addEventListener("click", function (e) {
    e.preventDefault();
    menuMensagem.style.display = "none";
    bootstrap.Modal.getOrCreateInstance(document.getElementById("modalExcluirMensagem")).show();
});

document.getElementById("confirmarExcluirMensagem").addEventListener("click", function () {
    fetch("/deleteMessage", {
        method: "POST",
        headers: {
            "Content-Type": "application/x-www-form-urlencoded"
        },
        body: "id=" + mensagemSelecionada
    })
    .then(response => response.text())
    .then(() => {
        bootstrap.Modal.getInstance(document.getElementById("modalExcluirMensagem")).hide();
        carregarMensagens();
        carregarConversas();
    });
});

// ==========================================
// EDITAR MENSAGEM
// ==========================================
let excluirImagemAtual = false; 
let arquivoNovaImagem = null;   

document.addEventListener("click", function(e){
    const botaoEditar = e.target.closest("#btnEditarMensagem");
    if(!botaoEditar){
        return;
    }
    e.preventDefault();

    document.getElementById("menuMensagem").style.display = "none";

    excluirImagemAtual = false;
    arquivoNovaImagem = null;
    document.getElementById("nomeNovaFoto").textContent = "";
    document.getElementById("inputAlterarFotoMensagem").value = "";

    document.getElementById("textoEditarMensagem").value = textoSelecionado;

    const divPreview = document.getElementById("previewImagemEditar");
    const imgModal = document.getElementById("imgEditarModal");
    const btnExcluirFoto = document.getElementById("btnExcluirFotoMensagem");

    if (imagemSelecionada) {
        imgModal.src = "/uploads/chat/" + imagemSelecionada;
        divPreview.style.display = "block";
        
        // Exibe a lixeira pois existe imagem original
        btnExcluirFoto.classList.remove("d-none");
        btnExcluirFoto.classList.add("d-block");
    } else {
        imgModal.src = "";
        divPreview.style.display = "none";
        
        // Oculta a lixeira
        btnExcluirFoto.classList.remove("d-block");
        btnExcluirFoto.classList.add("d-none");
    }

    bootstrap.Modal
        .getOrCreateInstance(document.getElementById("modalEditarMensagem"))
        .show();
});

document.getElementById("btnAlterarFotoMensagem").addEventListener("click", () => {
    document.getElementById("inputAlterarFotoMensagem").click();
});

document.getElementById("inputAlterarFotoMensagem").addEventListener("change", function() {
    const arquivo = this.files[0];
    if (!arquivo) return;

    arquivoNovaImagem = arquivo;
    document.getElementById("nomeNovaFoto").textContent = "Nova imagem: " + arquivo.name;

    const reader = new FileReader();
    reader.onload = function(e) {
        document.getElementById("imgEditarModal").src = e.target.result;
        document.getElementById("previewImagemEditar").style.display = "block";
        
        // Garante que o botão de lixeira apareça para a nova foto escolhida
        const btnExcluirFoto = document.getElementById("btnExcluirFotoMensagem");
        btnExcluirFoto.classList.remove("d-none");
        btnExcluirFoto.classList.add("d-block");
    };
    reader.readAsDataURL(arquivo);
    
    excluirImagemAtual = false; 
});

document.getElementById("btnExcluirFotoMensagem").addEventListener("click", function() {
    excluirImagemAtual = true;
    arquivoNovaImagem = null; 
    
    document.getElementById("inputAlterarFotoMensagem").value = "";
    document.getElementById("nomeNovaFoto").textContent = "";
    document.getElementById("imgEditarModal").src = "";
    document.getElementById("previewImagemEditar").style.display = "none";
    
    // Oculta novamente o botão já que a imagem foi removida do escopo do modal
    this.classList.remove("d-block");
    this.classList.add("d-none");
});

document.getElementById("confirmarEditarMensagem").addEventListener("click", function () {

    const texto = document.getElementById("textoEditarMensagem").value.trim();

    const formData = new FormData();

    formData.append("id", mensagemSelecionada);
    formData.append("mensagem", texto);
    formData.append("excluir_imagem", excluirImagemAtual ? "1" : "0");

    if (arquivoNovaImagem) {
        formData.append("nova_imagem", arquivoNovaImagem);
    }

    fetch("/editMessage", {
        method: "POST",
        body: formData
    })
    .then(response => response.json())
    .then(dados => {

        if (dados.status !== "ok") {
            return;
        }

        bootstrap.Modal
            .getInstance(document.getElementById("modalEditarMensagem"))
            .hide();

        // Atualiza SOMENTE a mensagem editada
        const bubble = document.querySelector(
            '.chat-me[data-id="' + mensagemSelecionada + '"]'
        );

        if (!bubble) {
            carregarMensagens();
            carregarConversas();
            return;
        }

        let html = "";

        if (dados.mensagem && dados.mensagem.length > 0) {
            html += `<div>${dados.mensagem.replace(/\n/g, "<br>")}</div>`;
        }

        if (dados.imagem && dados.imagem.length > 0) {
            html += `
                <div class="mt-2">
                    <img
                        src="/uploads/chat/${dados.imagem}?t=${Date.now()}"
                        class="img-fluid rounded"
                        style="max-width:250px;border-radius:15px;">
                </div>
            `;
        }

        bubble.innerHTML = html;

        // Atualiza os atributos usados pelo menu de contexto
        bubble.dataset.message = dados.mensagem;
        bubble.dataset.image = dados.imagem ?? "";

        // Atualiza as variáveis globais
        textoSelecionado = dados.mensagem;
        imagemSelecionada = dados.imagem ?? "";

        carregarConversas();

    });

});