// inclusão de comentário
document.querySelectorAll('.form-comentario').forEach(form => {
    form.addEventListener('submit', async function(e) {
        e.preventDefault();

        const formData = new FormData(this);
        const publicacaoId = formData.get('publicacao_id');

        try {
            const response = await fetch('/comment', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            const data = await response.json();

            if (data.success) {
                const container = this.closest('.comentarios-container');
                const novoComentario = document.createElement('div');
                
                // Adicionamos o ID dinâmico para os botões de editar/excluir funcionarem
                novoComentario.id = 'comentario-' + data.id; 
                novoComentario.className = 'd-flex mb-3 comentario-item';

                // Usamos a estrutura HTML idêntica à do PHP, com o dropdown!
                // O Replace no comentário garante que as quebras de linha funcionem e as aspas não quebrem o data-attribute
                novoComentario.innerHTML = `
                    <img src="${data.foto}" class="rounded-circle me-2" style="width:40px; height:40px; object-fit:cover;">
                    
                    <div class="flex-grow-1">
                        <div class="bg-light rounded-4 p-3 position-relative">
                            <strong>${data.nome}</strong>

                            <div class="dropdown position-absolute top-0 end-0 mt-2 me-2">
                                <button class="btn btn-sm border-0 bg-transparent text-muted p-0" data-bs-toggle="dropdown">
                                    <i class="bi bi-three-dots-vertical"></i>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end shadow border-0">
                                    <li>
                                        <button class="dropdown-item editar-comentario-btn" 
                                            data-id="${data.id}" 
                                            data-publicacao="${publicacaoId}" 
                                            data-comentario="${data.comentario.replace(/"/g, '&quot;')}">
                                            <i class="bi bi-pencil me-2"></i> Editar
                                        </button>
                                    </li>
                                    <li>
                                        <a class="dropdown-item text-danger btn-excluir-comentario" href="#" 
                                            data-id="${data.id}" 
                                            data-publicacao="${publicacaoId}">
                                            <i class="bi bi-trash me-2"></i> Excluir
                                        </a>
                                    </li>
                                </ul>
                            </div>

                            <div class="mt-1" id="texto-comentario-${data.id}">
                                ${data.comentario.replace(/\n/g, '<br>')}
                            </div>
                        </div>
                        
                        <small class="text-muted">
                            ${data.data}
                        </small>
                    </div>
                `;

                // Insere o comentário antes do formulário
                container.insertBefore(novoComentario, this);

                // Atualiza o contador no botão de balãozinho 💬
                const btnComentario = document.querySelector('.btn-comentario[data-id="' + publicacaoId + '"]');
                if (btnComentario) {
                    btnComentario.innerHTML = '💬 ' + data.total_comentarios;
                }

                // Limpa o textarea
                this.reset();
            }
        } catch (error) {
            console.error("Erro ao criar comentário:", error);
        }
    });
});

//exclusão de comentário
document.querySelectorAll('.btn-excluir-comentario').forEach(btn => {

    btn.addEventListener('click', async function(e) {

        e.preventDefault();

        if(!confirm('Deseja excluir este comentário?')){
            return;
        }

        const comentarioId = this.dataset.id;
        const publicacaoId = this.dataset.publicacao;

        const response = await fetch(
          '/deleteComment?id=' + comentarioId + '&publicacao_id=' + publicacaoId,
            {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            }
        );

        const data = await response.json();

        if(data.success){

            document
    .getElementById(
        'comentario-' + comentarioId
    )
    .remove();

const btnComentario = document.querySelector(
    '.btn-comentario[data-id="' +
    publicacaoId +
    '"]'
);

if(btnComentario){

    btnComentario.innerHTML =
        '💬 ' + data.total_comentarios;
        }
}
});
});


//edit comentário
  document.getElementById('formEditarComentario').addEventListener('submit', async function(e) {
    e.preventDefault();

    const form = this;
    const formData = new FormData(form);
    const comentarioId = document.getElementById('editComentarioId').value;
    const novoTexto = document.getElementById('editComentarioTexto').value;

    try {
        const response = await fetch(form.action, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        });

        // 1. Pega a div do comentário usando a mesma lógica da sua exclusão
        // e procura a div '.mt-1' que é onde fica o texto!
        const textContainer = document.querySelector('#comentario-' + comentarioId + ' .mt-1');
        
        if (textContainer) {
            textContainer.innerHTML = novoTexto.replace(/\n/g, '<br>');
        }

        // 2. Atualiza o texto guardado no botão de "Editar" para as próximas vezes
        const btnEditar = document.querySelector(`.editar-comentario-btn[data-id="${comentarioId}"]`);
        if (btnEditar) {
            btnEditar.dataset.comentario = novoTexto;
        }

        // 3. Fecha o modal simulando o clique no botão de fechar
        const modalElement = document.getElementById('editarComentarioModal');
        const btnClose = modalElement.querySelector('.btn-close');
        if (btnClose) {
            btnClose.click();
        }

    } catch (error) {
        console.error('Erro na requisição AJAX:', error);
        alert('Ocorreu um erro ao editar. Recarregue a página e tente novamente.');
    }
});