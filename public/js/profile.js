document.getElementById("confirmarAlteracaoStatus")?.addEventListener("click", function () {
    const btnStatus = document.getElementById("confirmarAlteracaoStatus");
    const usuarioId = btnStatus.getAttribute("data-usuario-id");
    const minhaSessionId = btnStatus.getAttribute("data-session-id");
    const statusAtual = btnStatus.getAttribute("data-status-atual");

    // Define qual será a próxima ação enviada ao Controller
    const novaAcao = (statusAtual === "ativo") ? "bloquear" : "ativar";

    fetch(`/deactivateAccount?id=${usuarioId}&acao=${novaAcao}`, {
        method: "POST",
        headers: {
            "X-Requested-With": "XMLHttpRequest"
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Se o ID da conta alterada for o meu próprio ID (auto-bloqueio), faz logout
            if (usuarioId === minhaSessionId) {
                window.location.href = "/";
            } else {
                // Se for o Admin gerenciando terceiros, avisa e recarrega a página atual
                alert(`Usuário alterado para ${novaAcao === "bloquear" ? "bloqueado" : "ativo"} com sucesso!`);
                window.location.reload();
            }
        } else {
            alert("Erro ao tentar alterar o status da conta.");
        }
    })
    .catch(error => {
        console.error("Erro na requisição:", error);
    });
});