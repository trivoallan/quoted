<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

use Slim\Factory\AppFactory;
use Slim\Views\Twig;
use Slim\Views\TwigMiddleware;
use function Codewithkyrian\Transformers\Pipelines\pipeline;

require __DIR__ . '/../vendor/autoload.php';


$app = AppFactory::create();
$twig = Twig::create(__DIR__ . '/../templates', ['cache' => false]);
$app->add(TwigMiddleware::create($app, $twig));

$app->get('/', function (Request $request, Response $response, $args) {
    $view = Twig::fromRequest($request);
    return $view->render($response, 'home.html.twig', [
        'name' => 'John',
    ]);
});

$app->get('/upload', function (Request $request, Response $response, $args) {
    $response->getBody()->write("Upload");
    return $response;
});

$app->post('/process', function (Request $request, Response $response, $args) {
    $response->getBody()->write("Process");
    return $response;
});

// Afficher le formulaire de traitement
$app->get('/analyze', function (Request $request, Response $response, $args) {
    $view = Twig::fromRequest($request);
    $defaultSource = <<<EOT
        Comme son nom l’indique, cette race de poney est originaire des îles Shetland situées au nord de l’Ecosse. Selon la légende, cet animal aurait survécu dans cet archipel depuis l’ère glaciaire. Ce poney pourrait aussi avoir migré de Scandinavie vers les îles Shetland il y a 8000 ans. C’est la raison pour laquelle il arbore un côté très rustique et un pelage épais capable de résister à des conditions climatiques extrêmes. C’est le cheval le plus puissant et résistant au monde. Certaines découvertes fossiles tendent à dire que son origine remonterait à l’Holocène, une période géologique interglaciaire. Le poney Shetland aurait donc pour lointains ancêtres les poneys nordiques de la toundra d’Europe du Nord. Il pourrait enfin être un descendant des chevaux naufragés de l’invincible Armada lors de la guerre anglo-espagnole de 1585. À l’heure actuelle, rien ne permet d’affirmer l’une ou l’autre de ces hypothèses.
    EOT;

    return $view->render($response, 'analyze.html.twig', [
        "defaultSource" => $defaultSource,
        "defaultModel" => "Xenova/bert-base-multilingual-cased-ner-hrl"
    ]);
});

// Récupèrer les informations du formulaire
$app->post('/analyze', function (Request $request, Response $response, $args) {
    $source = $request->getParsedBody()['source'];
    set_time_limit(120);
    ini_set('memory_limit', '512M');
    $summarizer = pipeline('summarization', 'Xenova/distilbart-cnn-6-6');

    $summary = $summarizer($source, maxNewTokens: 512, temperature: 0.7);
    var_dump($summary);
    // Rendu de la vue
    $view = Twig::fromRequest($request);
    return $view->render($response, 'analyze-post.html.twig', [
        'source' => $source,
        'output' => $summary
    ]);
});

$app->run();
