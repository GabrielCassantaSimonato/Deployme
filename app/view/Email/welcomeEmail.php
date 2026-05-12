<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">

    <style>
        body {
            margin: 0;
            padding: 0;
            background: #f4f7fb;
            font-family: Arial, Helvetica, sans-serif;
        }

        .email-wrapper {
            padding: 40px 20px;
        }

        .email-container {
            max-width: 600px;
            margin: auto;
            background: #ffffff;
            border-radius: 18px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
        }

        .email-header {
            background: linear-gradient(135deg, #2563eb, #1e40af);
            padding: 40px;
            text-align: center;
            color: white;
        }

        .email-header img {
            width: 90px;
            margin-bottom: 20px;
        }

        .email-header h1 {
            margin: 0;
            font-size: 32px;
            font-weight: 800;
        }

        .email-header p {
            margin-top: 10px;
            opacity: 0.9;
            font-size: 16px;
        }

        .email-body {
            padding: 40px;
            color: #1f2937;
        }

        .email-body h2 {
            margin-top: 0;
            font-size: 24px;
        }

        .email-body p {
            font-size: 16px;
            line-height: 1.7;
            color: #4b5563;
        }

        .feature-card {
            background: #eff6ff;
            border: 1px solid #bfdbfe;
            padding: 25px;
            border-radius: 14px;
            margin: 30px 0;
        }

        .feature-card h3 {
            margin-top: 0;
            color: #1d4ed8;
        }

        .feature-card ul {
            padding-left: 20px;
            color: #374151;
            line-height: 1.8;
        }

        .btn-area {
            text-align: center;
            margin-top: 40px;
        }

        .btn {
            background: #2563eb;
            color: white !important;
            text-decoration: none;
            padding: 16px 32px;
            border-radius: 12px;
            display: inline-block;
            font-weight: bold;
            font-size: 16px;
        }

        .email-footer {
            background: #111827;
            color: #9ca3af;
            padding: 25px;
            text-align: center;
            font-size: 13px;
        }
    </style>

</head>

<body>

    <div class="email-wrapper">

        <div class="email-container">

            <!-- HEADER -->
            <div class="email-header">


                <h1>Bem-vindo à Deployme 🚀</h1>

                <p>
                    A plataforma inteligente para talentos tech
                </p>

            </div>

            <!-- BODY -->
            <div class="email-body">

                <h2>
                    Olá,
                    <?= $nome ?>
                </h2>

                <p>
                    Seu cadastro foi realizado com sucesso na
                    <strong>Deployme</strong>.
                </p>

                <p>
                    Agora você já pode acessar a plataforma,
                    completar seu perfil e explorar oportunidades
                    incríveis na área de tecnologia.
                </p>

                <div class="feature-card">

                    <h3>
                        O que você já pode fazer:
                    </h3>

                    <ul>
                        <li>Completar perfil profissional</li>
                        <li>Analisar currículo com IA</li>
                        <li>Explorar vagas tech</li>
                        <li>Conectar-se com recrutadores e estudantes de TI</li>
                    </ul>

                </div>

                <div class="btn-area">

                    <a href="http://localhost:8080/" class="btn">
                        Acessar Deployme
                    </a>

                </div>

            </div>

            <!-- FOOTER -->
            <div class="email-footer">

                ©
                <?= date('Y') ?> Deployme • Todos os direitos reservados

            </div>

        </div>

    </div>

</body>

</html>