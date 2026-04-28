// --- CEP E VIACEP ---
        function mascaraCEP(campo) {
            let v = campo.value.replace(/\D/g, "");
            if (v.length > 5) v = v.substring(0, 5) + "-" + v.substring(5, 8);
            campo.value = v;
        }

        async function buscaViaCEP(cep) {
            cep = cep.replace(/\D/g, "");
            if (cep.length !== 8) return;
            try {
                const res = await fetch(`https://viacep.com.br/ws/${cep}/json/`);
                const data = await res.json();
                if (!data.erro) {
                    document.getElementById('rua').value = data.logradouro;
                    document.getElementById('bairro').value = data.bairro;
                    document.getElementById('cidade').value = data.localidade;
                    document.getElementById('uf').value = data.uf;
                    document.getElementById('complemento').focus();
                }
            } catch (e) { console.error("Erro na busca de CEP"); }
        }
        // --- GITHUB API ---
        const githubInput = document.getElementById('github-user');
        githubInput.addEventListener('input', (e) => {
            let timer;
            clearTimeout(timer);
            timer = setTimeout(async () => {
                let user = e.target.value.trim().replace('@','');
                if(!user) { document.getElementById('github-feedback').classList.add('d-none'); return; }
                document.getElementById('github-feedback').classList.remove('d-none');
                document.getElementById('github-status').innerText = 'Validando perfil...';
                try {
                    const res = await fetch(`https://api.github.com/users/${user}`);
                    if(res.ok) {
                        const data = await res.json();
                        document.getElementById('github-avatar').src = data.avatar_url;
                        document.getElementById('github-status').innerText = `Olá, ${data.name || user}!`;
                    } else { document.getElementById('github-status').innerText = 'Username não encontrado'; }
                } catch(err) { }
            }, 600);
        });

        // --- FICHEIROS ---
        document.getElementById('photo-file').addEventListener('change', e => {
            if(e.target.files[0]) document.getElementById('photo-text').innerText = "Selecionado: " + e.target.files[0].name;
        });
        document.getElementById('resume-file').addEventListener('change', e => {
            if(e.target.files[0]) {
                document.getElementById('resume-text').innerText = "PDF: " + e.target.files[0].name;
                document.getElementById('btnAnalyze').classList.remove('d-none');
            }
        });