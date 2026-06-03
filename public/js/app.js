// ==========================================
// FUNÇÕES DE CRIAÇÃO (Exibição de formulários e preview)
// ==========================================
function showPost() {
    document.getElementById('postForm').style.display = 'block';
    document.getElementById('vagaForm').style.display = 'none';
    document.getElementById('btnPost').classList.add('active');
    document.getElementById('btnVaga').classList.remove('active');
}

function showVaga() {
    document.getElementById('postForm').style.display = 'none';
    document.getElementById('vagaForm').style.display = 'block';
    document.getElementById('btnVaga').classList.add('active');
    document.getElementById('btnPost').classList.remove('active');
}

function previewPostImage(event) {
    const input = event.target;
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('previewImagem').src = e.target.result;
            document.getElementById('previewContainer').classList.remove('d-none');
        }
        reader.readAsDataURL(input.files[0]);
    }
}

function previewVagaImage(event) {
    const input = event.target;
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('previewImagemVaga').src = e.target.result;
            document.getElementById('previewContainerVaga').classList.remove('d-none');
        }
        reader.readAsDataURL(input.files[0]);
    }
}

// ==========================================
// FUNÇÕES DE EDIÇÃO (Executadas após a página carregar)
// ==========================================
document.addEventListener('DOMContentLoaded', function () {

    // ---------------------------------------------------------
    // 1. PREVIEW DINÂMICO PARA EDIÇÃO (POSTS E VAGAS JUNTOS)
    // ---------------------------------------------------------
    document.addEventListener('change', function(event) {
        
        // Se a mudança foi no input de imagem do POST
        if (event.target && event.target.id === 'editPostFileInput') {
            const input = event.target;
            if (input.files && input.files[0]) {
                const arquivo = input.files[0];
                if (!arquivo.type.startsWith('image/')) {
                    alert('Por favor, selecione um arquivo de imagem válido.');
                    input.value = ''; return;
                }
                const reader = new FileReader();
                reader.onload = function(e) {
                    const img = document.getElementById('editPreviewImagem');
                    const container = document.getElementById('editPreviewContainer');
                    if (img && container) {
                        img.src = e.target.result;
                        container.classList.remove('d-none');
                    }
                };
                reader.readAsDataURL(arquivo);
            }
        }

        // Se a mudança foi no input de imagem da VAGA
        if (event.target && event.target.id === 'editVagaFileInput') {
            const input = event.target;
            if (input.files && input.files[0]) {
                const arquivo = input.files[0];
                if (!arquivo.type.startsWith('image/')) {
                    alert('Por favor, selecione um arquivo de imagem válido.');
                    input.value = ''; return;
                }
                const reader = new FileReader();
                reader.onload = function(e) {
                    const img = document.getElementById('editVagaPreviewImagem');
                    const container = document.getElementById('editVagaPreviewContainer');
                    if (img && container) {
                        img.src = e.target.result;
                        container.classList.remove('d-none');
                    }
                };
                reader.readAsDataURL(arquivo);
            }
        }
    });

    // ---------------------------------------------------------
    // 2. ABRIR MODAL E PREENCHER DADOS - POST
    // ---------------------------------------------------------
    document.querySelectorAll('.editar-post-btn').forEach(btn => {
        btn.addEventListener('click', function () {
            const id = this.dataset.id;
            const texto = this.dataset.texto;
            const imagem = this.dataset.imagem;

            document.getElementById('editPostId').value = id;
            document.getElementById('editPostTexto').value = texto;

            const previewContainer = document.getElementById('editPreviewContainer');
            const previewImagem = document.getElementById('editPreviewImagem');

            const fileInput = document.getElementById('editPostFileInput');
            if (fileInput) fileInput.value = '';

            if (imagem && imagem !== '') {
                previewContainer.classList.remove('d-none');
                previewImagem.src = '/uploads/publicacoes/' + imagem;
            } else {
                previewContainer.classList.add('d-none');
                previewImagem.src = ''; 
            }

            const modalElement = document.getElementById('editarPostModal');
            if (modalElement) {
                 let modal = bootstrap.Modal.getInstance(modalElement);
                 if (!modal) modal = new bootstrap.Modal(modalElement);
                 modal.show();
            }
        });
    });

    // ---------------------------------------------------------
    // 3. ABRIR MODAL E PREENCHER DADOS - VAGA
    // ---------------------------------------------------------
    document.querySelectorAll('.editar-vaga-btn').forEach(btn => {
        btn.addEventListener('click', function () {
            const id = this.dataset.id;
            const titulo = this.dataset.titulo;
            const empresa = this.dataset.empresa;
            const texto = this.dataset.texto;
            const salario = this.dataset.salario;
            const localizacao = this.dataset.localizacao;
            const modalidade = this.dataset.modalidade;
            const imagem = this.dataset.imagem;

            document.getElementById('editVagaId').value = id;
            document.getElementById('editVagaTitulo').value = titulo;
            document.getElementById('editVagaEmpresa').value = empresa;
            document.getElementById('editVagaTexto').value = texto;
            document.getElementById('editVagaSalario').value = salario;
            document.getElementById('editVagaLocalizacao').value = localizacao;
            
            if (modalidade) {
                document.getElementById('editVagaModalidade').value = modalidade;
            } else {
                document.getElementById('editVagaModalidade').value = 'presencial';
            }

            const fileInput = document.getElementById('editVagaFileInput');
            if (fileInput) fileInput.value = '';

            const previewContainer = document.getElementById('editVagaPreviewContainer');
            const previewImagem = document.getElementById('editVagaPreviewImagem');

            if (imagem && imagem !== '') {
                previewContainer.classList.remove('d-none');
                previewImagem.src = '/uploads/publicacoes/' + imagem;
            } else {
                previewContainer.classList.add('d-none');
                previewImagem.src = '';
            }

            const modalElement = document.getElementById('editarVagaModal');
            if (modalElement) {
                 let modal = bootstrap.Modal.getInstance(modalElement);
                 if (!modal) modal = new bootstrap.Modal(modalElement);
                 modal.show();
            }
        });
    });

});

document.querySelectorAll('.btn-comentario').forEach(btn => {

    btn.addEventListener('click', function(){

        const id = this.dataset.id;

        const container =
            document.getElementById(
                'comentarios-' + id
            );

        container.classList.toggle('d-none');

    });

});