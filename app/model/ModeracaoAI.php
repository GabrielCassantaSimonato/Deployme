<?php

namespace app\model;

use MF\Model\Model;

class ModeracaoAI extends Model
{
    public function analisar($textoUsuario)
    {
        $apiKey = $_ENV['GEMINI_API_KEY_ANALYZE'];

        $modelo = 'gemini-2.5-flash';

        $apiUrl = "https://generativelanguage.googleapis.com/v1beta/models/$modelo:generateContent?key=$apiKey";

        $prompt = "
        Você é um moderador da rede social Deployme.
        Analise a seguinte publicação:
        '$textoUsuario'
        Regras da plataforma:
        - Bloquear conteúdos políticos, partidários ou eleitorais.
        - Bloquear discursos de ódio, preconceito, discriminação ou ataques a grupos.
        - Bloquear ofensas, xingamentos, ameaças ou assédio.
        - Bloquear conteúdos violentos ou que incentivem violência.
        - Bloquear conteúdos falando mal de instituições e empresas.
        - Permitir conteúdos relacionados a tecnologia, programação, carreira, estudo, vagas de emprego, experiências profissionais, networking e dúvidas técnicas.
        - Permitir saudações e conversas respeitosas.
            Se a publicação for bloqueada:
                - Explique exatamente qual regra foi violada.
                - Explique o trecho problemático encontrado.
                - Sugira uma forma adequada de reescrever o conteúdo.
        Retorne SOMENTE o JSON abaixo:
{
    \"aprovado\": true ou false,
    \"motivo\": \"explicação detalhada\"
}
";

        $data = [
            'contents' => [
                [
                    'parts' => [
                        [
                            'text' => $prompt
                        ]
                    ]
                ]
            ],
            'generationConfig' => [
                'temperature' => 0.1,
                'responseMimeType' => 'application/json'
            ]
        ];

        $ch = curl_init($apiUrl);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json'
        ]);

        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        $response = curl_exec($ch);

        curl_close($ch);

        $jsonResponse = json_decode($response, true);

        if (
            isset(
            $jsonResponse['candidates'][0]['content']['parts'][0]['text']
        )
        ) {

            return json_decode(
                $jsonResponse['candidates'][0]['content']['parts'][0]['text'],
                true
            );
        }

        return [
            'aprovado' => false,
            'motivo' => 'Erro ao analisar conteúdo.'
        ];
    }
}