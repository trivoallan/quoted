<?php
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

use Slim\Factory\AppFactory;
use Slim\Views\Twig;
use Slim\Views\TwigMiddleware;
use TextRazor\TextRazor as TextRazorClient;
use TextRazorSettings as GlobalTextRazorSettings;

require __DIR__ . '/../vendor/autoload.php';

GlobalTextRazorSettings::setApiKey('1cb19ca82e4909b5a3a97c900a2a1303c35c813f420a79bfeb9322f1');

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
    return $view->render($response, 'analyze.html.twig', [
        'name' => 'John',
    ]);
});

// Récupèrer les informations du formulaire
$app->post('/analyze', function (Request $request, Response $response, $args) {
    $source = $request->getParsedBody()['source'];

    // Analyse du texte
    $textrazor = new TextRazorClient();
    $textrazor->addExtractor('entities');
    $result = $textrazor->analyze($source);

    if (isset($result['response']['entities'])) {
        foreach ($result['response']['entities'] as $entity) {
            print_r($entity['entityId'] . PHP_EOL);
        }
    };
    // Rendu de la vue
    $view = Twig::fromRequest($request);
    return $view->render($response, 'analyze-post.html.twig', [
        'source' => $source,
        'result' => $result
    ]);
});

$app->run();