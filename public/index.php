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
    return $view->render($response, 'analyze.html.twig', [
        'name' => 'John',
    ]);
});

// Récupèrer les informations du formulaire
$app->post('/analyze', function (Request $request, Response $response, $args) {
    $source = $request->getParsedBody()['source'];
    $classifier = pipeline('sentiment-analysis');
    $result = $classifier($source);
    // Rendu de la vue
    $view = Twig::fromRequest($request);
    return $view->render($response, 'analyze-post.html.twig', [
        'source' => $source,
        'result' => $result
    ]);
});

$app->run();