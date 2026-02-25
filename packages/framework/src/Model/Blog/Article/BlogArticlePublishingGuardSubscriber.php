<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Blog\Article;

use Override;
use Psr\Clock\ClockInterface;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Workflow\Event\GuardEvent;
use Symfony\Component\Workflow\Event\TransitionEvent;

class BlogArticlePublishingGuardSubscriber implements EventSubscriberInterface
{
    public function __construct(
        protected readonly Domain $domain,
        protected readonly ClockInterface $clock,
    ) {
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public static function getSubscribedEvents(): array
    {
        return [
            'workflow.blog_article_publishing.guard.publish' => 'guardPublish',
            'workflow.blog_article_publishing.guard.to_preview' => 'guardToPreview',
            'workflow.blog_article_publishing.transition.publish' => 'setPublishDateOnPublish',
        ];
    }

    public function guardPublish(GuardEvent $event): void
    {
        /** @var \Shopsys\FrameworkBundle\Model\Blog\Article\BlogArticleDomain $blogArticleDomain */
        $blogArticleDomain = $event->getSubject();

        $this->guardName($event, $blogArticleDomain);
    }

    public function guardToPreview(GuardEvent $event): void
    {
        /** @var \Shopsys\FrameworkBundle\Model\Blog\Article\BlogArticleDomain $blogArticleDomain */
        $blogArticleDomain = $event->getSubject();

        $this->guardName($event, $blogArticleDomain);
    }

    protected function guardName(GuardEvent $event, BlogArticleDomain $blogArticleDomain): void
    {
        $locale = $this->domain->getDomainConfigById($blogArticleDomain->getDomainId())->getLocale();

        $name = $blogArticleDomain->getBlogArticle()->getName($locale);

        if ($name === null || $name === '') {
            $event->setBlocked(true, t('Name must be defined.'));
        }
    }

    public function setPublishDateOnPublish(TransitionEvent $event): void
    {
        /** @var \Shopsys\FrameworkBundle\Model\Blog\Article\BlogArticleDomain $blogArticleDomain */
        $blogArticleDomain = $event->getSubject();

        if ($blogArticleDomain->getPublishDate() === null) {
            $blogArticleDomain->setPublishDate($this->clock->now());
        }
    }
}
