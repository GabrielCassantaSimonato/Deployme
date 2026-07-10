from locust import HttpUser, task, between

class DeploymeUser(HttpUser):

    # Simula o intervalo entre as ações de um usuário real
    wait_time = between(2, 5)

    def on_start(self):
        """
        Executado apenas uma vez quando o usuário virtual é criado.
        Realiza a autenticação para que todas as próximas
        requisições sejam feitas com uma sessão válida.
        """

        self.client.post(
            "/auth",
            data={
                "email": "simonato35@gmail.com",
                "senha": "teste1234"
            },
            allow_redirects=True
        )

    @task(8)
    def acessar_timeline(self):
        """
        A Timeline é a funcionalidade mais acessada da plataforma.
        Recebe maior peso para representar o comportamento esperado
        dos usuários.
        """
        self.client.get("/timeline")

    @task(2)
    def acessar_chat(self):
        """
        Simula o acesso ao sistema de mensagens privadas.
        """
        self.client.get("/chat")

    @task(3)
    def acessar_vagas(self):
        """
        Simula a consulta das vagas disponíveis.
        """
        self.client.get("/viewVacancies")