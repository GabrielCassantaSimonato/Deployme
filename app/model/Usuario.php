<?php

namespace app\model;

use MF\model\Model;

class Usuario extends Model
{

    private $id;
    private $nome;
    private $email;
    private $senha;
    private $tipo;
    private $genero_id;
    private $foto;
    private $criado_em;

    // GET e SET mágico
    public function __get($atributo)
    {
        return $this->$atributo;
    }

    public function __set($atributo, $valor)
    {
        $this->$atributo = $valor;
    }

    public function salvarUsuario()
    {
        $query = "INSERT INTO usuarios 
        (nome, email, senha, tipo, genero_id, foto, criado_em)
        VALUES 
        (:nome, :email, :senha, :tipo, :genero_id, :foto, NOW())";

        $stmt = $this->db->prepare($query);

        $stmt->bindValue(':nome', $this->__get('nome'));
        $stmt->bindValue(':email', $this->__get('email'));
        $stmt->bindValue(':senha', $this->__get('senha'));
        $stmt->bindValue(':tipo', $this->__get('tipo'));
        $stmt->bindValue(':genero_id', $this->__get('genero_id'));
        $stmt->bindValue(':foto', $this->__get('foto'));

        $stmt->execute();

        return $this->db->lastInsertId();
    }

    public function emailExiste()
    {
        $query = "SELECT id FROM usuarios WHERE email = :email";
        $stmt = $this->db->prepare($query);
        $stmt->bindValue(':email', $this->__get('email'));
        $stmt->execute();

        return $stmt->rowCount() > 0;
    }

    public function validarCadastro()
    {

        $erros = [];

        if (empty($this->__get('nome'))) {
            $erros['nome'] = 'Nome é obrigatório';
        }

        if (empty($this->__get('email'))) {
            $erros['email'] = 'Email é obrigatório';
        }

        if ($this->emailExiste()) {
            $erros['email'] = 'Email já cadastrado';
        }

        if (strlen($this->__get('senha')) < 8) {
            $erros['senha'] = 'Senha deve ter no mínimo 8 caracteres';
        }

        return $erros;
    }

    public function buscarPorEmail()
    {
        $query = "
        SELECT *
        FROM usuarios
        WHERE email = :email
    ";

        $stmt = $this->db->prepare($query);

        $stmt->bindValue(':email', $this->__get('email'));

        $stmt->execute();

        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    public function buscarUsuarioCompleto($id)
    {
        $query = "
        SELECT

    u.id,
    u.nome,
    u.email,
    u.tipo,
    u.foto,

    e.cep,
    e.rua,
    e.bairro,
    e.complemento,
    e.cidade,
    e.uf,
    e.github,
    e.curriculo,

    e.universidade_id,
    e.curso_id,
    e.semestre_id,

    f.nome AS faculdade,

    c.nome AS curso,

    s.semestre AS semestre,

    r.empresa

FROM usuarios u

LEFT JOIN estudantes e
    ON e.usuario_id = u.id

LEFT JOIN recrutadores r
    ON r.usuario_id = u.id

LEFT JOIN universidades f
    ON e.universidade_id = f.id

LEFT JOIN cursos c
    ON e.curso_id = c.id

LEFT JOIN semestres s
    ON e.semestre_id = s.id

WHERE u.id = :id
    ";

        $stmt = $this->db->prepare($query);

        $stmt->bindValue(':id', $id);

        $stmt->execute();

        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    public function updateProfile($dados)
    {
        // USUÁRIOS
        $query = "

        UPDATE usuarios SET

            nome = :nome,
            email = :email

        WHERE id = :id

    ";

        if (isset($dados['foto'])) {

            $query = "

            UPDATE usuarios SET

                nome = :nome,
                email = :email,
                foto = :foto

            WHERE id = :id

        ";

        }

        $stmt = $this->db->prepare($query);

        $stmt->bindValue(':id', $dados['id']);
        $stmt->bindValue(':nome', $dados['nome']);
        $stmt->bindValue(':email', $dados['email']);

        if (isset($dados['foto'])) {

            $stmt->bindValue(':foto', $dados['foto']);

        }

        $stmt->execute();

        // ESTUDANTE
        if ($_SESSION['tipo'] == 'estudante') {

            $queryEstudante = "

            UPDATE estudantes SET

                github = :github,
                cep = :cep,
                rua = :rua,
                bairro = :bairro,
                complemento = :complemento,
                cidade = :cidade,
                uf = :uf,
                universidade_id = :universidade_id,
                curso_id = :curso_id,
                semestre_id = :semestre_id

        ";

            if (isset($dados['curriculo'])) {

                $queryEstudante .= ",
                curriculo = :curriculo
            ";

            }

            $queryEstudante .= "

            WHERE usuario_id = :usuario_id

        ";

            $stmt = $this->db->prepare($queryEstudante);

            $stmt->bindValue(':github', $dados['github']);
            $stmt->bindValue(':cep', $dados['cep']);
            $stmt->bindValue(':rua', $dados['rua']);
            $stmt->bindValue(':bairro', $dados['bairro']);
            $stmt->bindValue(':complemento', $dados['complemento']);
            $stmt->bindValue(':cidade', $dados['cidade']);
            $stmt->bindValue(':uf', $dados['uf']);
            $stmt->bindValue(':universidade_id', $dados['universidade_id']);
            $stmt->bindValue(':curso_id', $dados['curso_id']);
            $stmt->bindValue(':semestre_id', $dados['semestre_id']);

            if (isset($dados['curriculo'])) {

                $stmt->bindValue(':curriculo', $dados['curriculo']);

            }

            $stmt->bindValue(':usuario_id', $dados['id']);

            $stmt->execute();

        }

        // RECRUTADOR
        if ($_SESSION['tipo'] == 'recrutador') {

            $query = "

            UPDATE recrutadores SET

                empresa = :empresa

            WHERE usuario_id = :usuario_id

        ";

            $stmt = $this->db->prepare($query);

            $stmt->bindValue(':empresa', $dados['empresa']);
            $stmt->bindValue(':usuario_id', $dados['id']);

            $stmt->execute();

        }
    }

    public function atualizarSenha()
    {
        $query = "
        UPDATE usuarios
        SET senha = :senha
        WHERE email = :email
    ";

        $stmt = $this->db->prepare($query);

        $stmt->bindValue(
            ':email',
            $this->__get('email')
        );

        $stmt->bindValue(
            ':senha',
            $this->__get('senha')
        );

        return $stmt->execute();
    }

    public function listarUsuarios()
    {
        $query = "
        SELECT
            u.id,
            u.nome,
            u.foto,
            u.tipo,

            e.cidade,
            e.uf,

            c.nome AS curso,
            uni.nome AS universidade,

            r.empresa,

            CASE
                WHEN s.id IS NULL THEN 0
                ELSE 1
            END AS seguindo

        FROM usuarios u

        LEFT JOIN estudantes e
            ON e.usuario_id = u.id

        LEFT JOIN cursos c
            ON c.id = e.curso_id

        LEFT JOIN universidades uni
            ON uni.id = e.universidade_id

        LEFT JOIN recrutadores r
            ON r.usuario_id = u.id

        LEFT JOIN seguidores s
            ON s.seguindo_id = u.id
            AND s.usuario_id = :usuario_logado

        WHERE
            u.id <> :usuario_logado

        AND
            u.tipo <> 'admin'

        ORDER BY u.nome ASC
    ";

        $stmt = $this->db->prepare($query);

        $stmt->bindValue(
            ':usuario_logado',
            $_SESSION['id']
        );

        $stmt->execute();

        return $stmt->fetchAll(
            \PDO::FETCH_ASSOC
        );
    }

}


?>