<?php

declare(strict_types=1);

namespace RestBundle\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Convert request to Json.
 */
class RequestSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => ['onKernelRequest', 254],
        ];
    }

    /**
     * @throws BadRequestHttpException
     */
    public function onKernelRequest(RequestEvent $event): void
    {
        $request = $event->getRequest();
        if (!in_array($request->getContentTypeFormat(), $this->supportsTypes())) {
            return;
        }
        $content = $request->headers->has('data') ? $request->headers->get('data') : $request->getContent();
        $data = json_decode($content, true);
        if (false === $data) {
            throw new BadRequestHttpException('Request data is invalid');
        }

        $request->request->replace(is_array($data) ? $data : []);
    }

    private function supportsTypes(): array
    {
        return ['json', 'application/json'];
    }
}
