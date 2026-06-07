document.querySelectorAll('.btn-seguir').forEach(btn => {

    btn.addEventListener('click',async function()
{

        const usuarioId = this.dataset.id;

const seguindo = this.dataset.seguindo == '1';

const url = seguindo ? '/unFollow' : '/follow';
const formData = new FormData();
formData.append('usuario_id', usuarioId);
const response = await fetch( url,
    {
        method: 'POST',
        body: formData,
        headers: {'X-Requested-With': 'XMLHttpRequest'}
    }
);

const data = await response.json();
if(data.success){
    if(seguindo){
    this.innerText ='Seguir';
    this.dataset.seguindo ='0';
    this.classList.remove('btn-danger');
    this.classList.add('btn-primary');
}else{
    this.innerText ='Deixar de seguir';
    this.dataset.seguindo ='1';
    this.classList.remove('btn-primary');
    this.classList.add('btn-danger');
     }
}
});
});