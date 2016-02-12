<?php
namespace Oxide\Http;

use Oxide\Common\ServiceContainer;

/**
 * Context class
 *
 */
class Context extends ServiceContainer
{

    /**
     * Create http context
     *
     * @param Request $request
     * @param Response $response
     * @param Session $session
     */
    public function __construct(Request $request, Response $response = null, Session $session = null)
    {
        return;
        parent::__construct();
        // bind self instance
        $this->bind([self::class, 'context'], $this);
        // bind request with alias
        $this->bind([Request::class, 'request'], $request);

        // bind response if not already provided
        if (!$response) {
            $response = Response::class;
        }
        $this->bind([Response::class, 'response']);


        // bind session if not already provided
        if (!$session) {
            $this->bind('session', function (Request $request) {
                return new Session([
                    'cookie_domain' => $request->getUriComponents(Request::URI_HOST),
                    'cookie_secure' => $request->isSecured()
                ]);
            });
        } else {
            $this['session'] = $session;
        }

        // bind the auth
        $this->bind('auth', function (Session $session) {
            return new Auth(new AuthStorage($session));
        });
    }
}