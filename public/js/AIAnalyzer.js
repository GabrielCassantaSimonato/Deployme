const resumeInput = document.getElementById('resume-file');
const analyzeBtn = document.getElementById('btnAnalyze');

resumeInput.addEventListener('change', function () {

    if (resumeInput.files.length > 0) {
        analyzeBtn.classList.remove('d-none');
    } else {
        analyzeBtn.classList.add('d-none');
    }

});

// ANALISAR CURRÍCULO
analyzeBtn.addEventListener('click', async () => {

    const arquivo = resumeInput.files[0];

    if (!arquivo) {
        alert('Selecione um currículo');
        return;
    }

    const formData = new FormData();
    formData.append('curriculo', arquivo);

    try {

        analyzeBtn.innerHTML = 'Analisando...';
        analyzeBtn.disabled = true;

        const response = await fetch('/resumeAnalyzer', {
            method: 'POST',
            body: formData
        });

        const texto = await response.text();
        const data = JSON.parse(texto);

        // POPULA MODAL
        document.getElementById('aiScore').innerText =
        data.score + '/10';

        document.getElementById('aiFeedback').innerText =
        data.feedback;

        document.getElementById('aiStrong').innerText =
        data.pontos_fortes;

        document.getElementById('aiImprove').innerText =
        data.melhoria;

        // ABRIR MODAL
        const modal = new bootstrap.Modal(
        document.getElementById('aiModal')
);

modal.show();

    } catch (erro) {

        console.error(erro);
        alert('Erro ao analisar currículo');

    } finally {

        analyzeBtn.innerHTML =
            '<i class="bi bi-stars me-1"></i> Analisar com IA';

        analyzeBtn.disabled = false;
    }

});