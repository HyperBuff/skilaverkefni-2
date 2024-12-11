<?php

namespace Drupal\music_search\EventSubscriber;

use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Drupal\Core\Routing\LocalRedirectResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\KernelEvents;
use Drupal\Core\Url;
use Symfony\Component\HttpKernel\Event\ResponseEvent;


class MusicSearchRedirectSubscriber implements EventSubscriberInterface {
 /**
  * The current user
  *
  * @var AccountProxyInterface
  */
 protected $currentUser;

  /**
   * The current route match
   *
   * @var RouteMatchInterface
   */
  protected $routeMatch;

  /**
   * MusicSearchRedirectSubscriber constructor
   *
   * @param \Drupal\Core\Session\AccountProxyInterface $currentUser
   *  The current user.
   * @param \Drupal\Core\Routing\RouteMatchInterface $routeMatch
   *  The current Route match.
   */

  public function __construct(AccountProxyInterface $current_user, RouteMatchInterface $route_match) {
    $this->currentUser = $current_user;
    $this->routeMatch = $route_match;
  }

  /**
   * {@inheritdoc }
   */
  public static function getSubscribedEvents() {
    $events[KernelEvents::RESPONSE][] = array('onRequest', 0);
    return $events;
  }

  /**
   * Handler for the kernel request event.
   *
   * @param \Symfony\Component\HttpKernel\Event\RequestEvent $event
   *  The request event
   */

  public function onRequest(ResponseEvent $event) {
    $route_name = $this->routeMatch->getRouteName();

    if ($route_name == 'music_search.search') {
      return;
    }

    $roles = $this->currentUser->getRoles();
    if (in_array('non_grata', $roles)) {
      $url =  Url::fromUri('internal:/');
      $event->setResponse(new LocalRedirectResponse($url->toString()));
    }

  }




}
