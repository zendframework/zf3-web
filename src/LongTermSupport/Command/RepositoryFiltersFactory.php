<?php
declare(strict_types=1);

namespace LongTermSupport\Command;

class RepositoryFiltersFactory
{
    /**
     * @var string[]
     */
    private $blacklist = [
        'zendframework/zendframework',
        'zendframework/zendframework.github.io',
        'zendframework/zend-coding-standard',
        'zendframework/zend-version',
        'zendframework/zf1',
        'zendframework/zf1-extras',
        'zendframework/zf2-documentation',
        'zendframework/zf2-tutorial',
        'zendframework/zf3-web',
        'zendframework/zfbot',
        'zendframework/zf-composer-repository',
        'zendframework/zf-mkdoc-theme',
        'zendframework/zf-web',
        'zfcampus/zendcon-design-patterns',
        'zfcampus/zf-angular',
        'zfcampus/zf-apigility-example',
        'zfcampus/zf-apigility-welcome',
    ];

    /**
     * @var string[]
     */
    private $whitelist = [
        'zendframework/ZendService_Amazon',
        'zendframework/ZendService_Apple_Apns',
        'zendframework/ZendService_Google_Gcm',
        'zendframework/ZendService_ReCaptcha',
        'zendframework/ZendService_Twitter',
        'zendframework/ZendSkeletonApplication',
        'zendframework/ZendXml',
    ];

    /**
     * @return callable[]
     */
    public function __invoke() : array
    {
        return [
            new NonZedRepositoryFilter($this->whitelist),
            new BlacklistRepositoryFilter($this->blacklist),
        ];
    }
}
