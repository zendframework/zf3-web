<?php

declare(strict_types=1);

namespace LongTermSupport;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Zend\Diactoros\Response\HtmlResponse;
use Zend\Expressive\Template\TemplateRendererInterface;

class LongTermSupportAction implements RequestHandlerInterface
{
    /**
     * @var string
     */
    private $packagesFile;

    /**
     * @var TemplateRendererInterface
     */
    private $renderer;

    public function __construct(TemplateRendererInterface $renderer, string $packagesFile)
    {
        $this->renderer = $renderer;
        $this->packagesFile = $packagesFile;
    }

    /**
     * {@inheritDoc}
     */
    public function handle(ServerRequestInterface $request) : ResponseInterface
    {
        return new HtmlResponse($this->renderer->render(
            'app::long-term-support',
            ['packages' => json_decode(file_get_contents($this->packagesFile), true)]
        ));
    }
}
