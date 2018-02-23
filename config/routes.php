<?php

declare(strict_types=1);

/**
 * Expressive routed middleware
 */

return function (
    \Zend\Expressive\Application $app,
    \Zend\Expressive\MiddlewareFactory $factory,
    \Psr\Container\ContainerInterface $container
) : void {
    $app->get('/api/zf-version', \App\Action\ApiAction::class, 'api-zf-version');
    $app->get('/', \App\Action\HomePageAction::class, 'home');
    $app->get('/blog[/:file]', \App\Action\BlogAction::class, 'blog');
    $app->get('/about[/faq]', \App\Action\AboutAction::class, 'about');
    $app->get('/license', \App\Action\AboutAction::class, 'license');
    $app->get('/long-term-support', \App\Action\AboutAction::class, 'long-term-support');
    $app->get('/security[/:action]', \App\Action\SecurityAction::class, 'security');
    $app->get('/security/advisory/:advisory', \App\Action\AdvisoryAction::class, 'advisory');
    $app->get('/changelog[/:changelog]', \App\Action\ChangelogAction::class, 'changelog');
    $app->get('/issues[/:type[/:issue]]', \App\Action\IssueAction::class, 'issue');
    $app->get('/manual/:version/:lang/:page[/:subpage]', \App\Action\ManualAction::class, 'manual');
    $app->post('/switch-manual', \App\Action\SwitchManualAction::class, 'switch-manual');
    $app->get('/learn[/:page]', \App\Action\LearnAction::class, 'learn');
    $app->get('/docs[/api/:ver]', \App\Action\DocsAction::class, 'docs');
    $app->get('/downloads[/:page]', \App\Action\InstallAction::class, 'install');
    $app->get('/participate[/:page]', \App\Action\ParticipateAction::class, 'participate');
    $app->get('/status', \App\Action\StatusAction::class, 'status');
    $app->get('/stats', \App\Action\StatisticsAction::class, 'statistics');
};
