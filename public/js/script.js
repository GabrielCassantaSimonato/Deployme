 // Lógica da Navbar (Efeito de Scroll)
        window.addEventListener('scroll', () => {
            const nav = document.getElementById('mainNav');
            if (nav.style.display !== 'none') {
                if (window.scrollY > 40) nav.classList.add('scrolled');
                else nav.classList.remove('scrolled');
            }
        });

        // Validação API GitHub
        const githubInput = document.getElementById('github-user');
        const feedbackArea = document.getElementById('github-feedback');
        const avatarImg = document.getElementById('github-avatar');
        const statusSpan = document.getElementById('github-status');
        let debounceTimer;

        async function checkGitHubUser(username) {
            let cleanUsername = username.trim().replace(/^@/, '');
            if (!cleanUsername) { feedbackArea.style.display = 'none'; return; }

            feedbackArea.style.display = 'flex';
            statusSpan.innerText = 'Consultando GitHub...';
            statusSpan.className = 'small fw-800 text-muted';
            avatarImg.style.display = 'none';

            try {
                const response = await fetch(`https://api.github.com/users/${cleanUsername}`);
                if (response.status === 200) {
                    const data = await response.json();
                    statusSpan.innerText = `Olá, ${data.name || cleanUsername}!`;
                    statusSpan.className = 'small fw-800 text-success';
                    avatarImg.src = data.avatar_url;
                    avatarImg.style.display = 'block';
                } else {
                    statusSpan.innerText = 'Usuário não localizado';
                    statusSpan.className = 'small fw-800 text-danger';
                    avatarImg.style.display = 'none';
                }
            } catch (e) {
                statusSpan.innerText = 'API Offline';
            }
        }

        githubInput.addEventListener('input', (e) => {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(() => checkGitHubUser(e.target.value), 600);
        });

        // Submit Simulado
        document.getElementById('registrationForm').addEventListener('submit', (e) => {
            e.preventDefault();
            const btn = e.target.querySelector('button');
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Processando...';
            btn.disabled = true;

            setTimeout(() => {
                alert('Bem-vindo à Deployme 2026!');
                showView('home');
                btn.innerHTML = 'Criar Perfil Agora';
                btn.disabled = false;
            }, 1500);
        });