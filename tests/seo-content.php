<?php
declare(strict_types=1);
putenv('CLICOMPUTER_GA4_ID=disabled');
require __DIR__ . '/run.php';

foreach ($model->pages() as $page) {
    if (isset($page['parent'])) {
        $graph = (new App\Core\Seo($model))->graph($page)['@graph'];
        $breadcrumbs = array_values(array_filter($graph, static fn ($item) => $item['@type'] === 'BreadcrumbList'))[0]['itemListElement'];
        check(array_column($breadcrumbs, 'position') === [1, 2, 3], 'Incorrect nested breadcrumb positions');
        check($breadcrumbs[1]['item'] === $model->settings()['url'] . '/' . $page['parent']['file'], 'Incorrect parent in schema');
    }
}
check(!str_contains($app->handle('GET', '/')->body, '24/7'), 'Unsupported support hours remain');
check(str_contains($app->handle('GET', '/contacto.html')->body, $model->settings()['hours_display']), 'Contact hours missing');
check(!str_contains($app->handle('GET', '/')->body, 'analyticsConfig'), 'Analytics is enabled without a real property');
putenv('CLICOMPUTER_GA4_ID=G-TEST1234');
$_SERVER['HTTP_HOST'] = 'www.clcomputer.com';
$withAnalytics = new App\Core\Application($mailer, $rateLimiter);
check(str_contains($withAnalytics->handle('GET', '/contacto.html')->body, 'analyticsConfig'), 'Configured analytics missing');
check(!str_contains($withAnalytics->handle('GET', '/buscar?q=private')->body, 'analyticsConfig'), 'Search query page has analytics');
check(!str_contains($withAnalytics->handle('GET', '/404-private')->body, 'analyticsConfig'), '404 page has analytics');
check(!str_contains($withAnalytics->handle('POST', '/contacto/enviar', $valid)->body, 'analyticsConfig'), 'Contact response has analytics');
$_SERVER['HTTP_HOST'] = '127.0.0.1:8780';
check(!str_contains($withAnalytics->handle('GET', '/contacto.html')->body, 'analyticsConfig'), 'Local preview sends production analytics');
unset($_SERVER['HTTP_HOST']);
putenv('CLICOMPUTER_GA4_ID');
echo "OK: jerarquía editorial, horarios y activación controlada de medición.\n";
