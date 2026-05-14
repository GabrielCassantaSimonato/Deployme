document.addEventListener('DOMContentLoaded', async () => {

    const reposContainer = document.getElementById('githubRepos');

    if(!reposContainer) return;

    const github = reposContainer.dataset.github;
    console.log(github);

    if(!github) return;

    try {

        const response = await fetch(
            `https://api.github.com/users/${github}/repos`
        );

        const repos = await response.json();

        reposContainer.innerHTML = '';

        if(repos.length === 0){

            reposContainer.innerHTML = `
                <div class="text-muted">
                    Nenhum repositório encontrado.
                </div>
            `;

            return;
        }

        repos.slice(0, 6).forEach(repo => {

            reposContainer.innerHTML += `

                <a 
                    href="${repo.html_url}" 
                    target="_blank"
                    class="github-repo-card"
                >

                    <div class="repo-top">

                        <i class="bi bi-book"></i>

                        <strong>
                            ${repo.name}
                        </strong>

                    </div>

                    <p>
                        ${repo.description ?? 'Sem descrição'}
                    </p>

                    <div class="repo-footer">

                        ⭐ ${repo.stargazers_count}

                    </div>

                </a>

            `;

        });

    } catch(error){

        reposContainer.innerHTML = `
            <div class="text-danger">
                Erro ao carregar repositórios.
            </div>
        `;
    }

});