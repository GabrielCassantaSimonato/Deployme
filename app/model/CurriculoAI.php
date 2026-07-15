<?php

namespace app\model;

use MF\model\Model;

class CurriculoAI
{
    /**
     * Envia o texto extraído do currículo para a API do Google Gemini para avaliação profissional.
     * 
     * Configura e consome a API do Gemini utilizando curl para realizar a análise de perfil,
     * definindo parâmetros de comportamento específicos para forçar o retorno estruturado
     * estritamente como um objeto JSON.
     */
    public static function analisarCurriculo($textoCurriculo)
    {
        $apiKey = $_ENV['GEMINI_API_KEY_ANALYZE'];
        $modelo = 'gemini-2.5-flash';
        $url = "https://generativelanguage.googleapis.com/v1beta/models/{$modelo}:generateContent?key={$apiKey}";

        $prompt = "
        Atue como um recrutador técnico sênior da plataforma Deployme.
        Analise o currículo abaixo e gere uma avaliação profissional.
        Quero um feedback humano, moderno e construtivo.
        Avalie:
        - clareza do currículo
        - organização
        - tecnologias
        - impacto profissional
        - maturidade técnica
        - chances no mercado
        Currículo:
            {$textoCurriculo}
        Responda SOMENTE um JSON válido no formato:
{
    \"nome\": \"\",
    \"feedback\": \"\",
    \"pontos_fortes\": \"\",
    \"melhoria\": \"\",
    \"score\": 0
}
";

        $body = [
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

        $curl = curl_init();

        curl_setopt_array($curl, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($body),
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json'
            ]
        ]);

        $response = curl_exec($curl);

        curl_close($curl);

        $responseData = json_decode($response, true);

        $textoIA = $responseData['candidates'][0]['content']['parts'][0]['text'] ?? null;

        return json_decode($textoIA, true);
    }
}