<?php
namespace app\controller;

use MF\controller\Action;
use MF\model\Container;
use app\model\CurriculoAI;
use Smalot\PdfParser\Parser;

class IAController extends Action
{
    public function resumeAnalyzer()
    {

        header('Content-Type: application/json');

        if (!isset($_FILES['curriculo'])) {
            echo json_encode([
                'erro' => 'Arquivo não enviado'
            ]);
            return;
        }

        $tmp = $_FILES['curriculo']['tmp_name'];

        $parser = new Parser();

        $pdf = $parser->parseFile($tmp);

        $texto = $pdf->getText();

        $resultado = CurriculoAI::analisarCurriculo($texto);

        echo json_encode($resultado);
    }
}