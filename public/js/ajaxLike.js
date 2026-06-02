document.addEventListener('click', async function(e){

    const btn = e.target.closest('.btn-curtir');

    if(!btn) return;

    e.preventDefault();

    const publicacao = btn.dataset.id;
    const curtido = btn.dataset.curtido;

    const url = curtido == '1'
        ? '/unlike'
        : '/like';

    const response = await fetch(url,{
        method:'POST',
        headers:{
            'Content-Type':'application/x-www-form-urlencoded'
        },
        body:'publicacao=' + publicacao
    });

    const data = await response.json();

    if(data.status === 'ok')
    {
        btn.dataset.curtido =
            curtido == '1' ? '0' : '1';

        btn.querySelector('.total').innerText =
            data.total;

        btn.querySelector('.icone').innerText =
            curtido == '1' ? '🤍' : '❤️';
    }
});