<?php

namespace Enhavo\Bundle\UserBundle\Endpoint\Type\Login;

use Enhavo\Bundle\ApiBundle\Data\Data;
use Enhavo\Bundle\ApiBundle\Endpoint\AbstractEndpointType;
use Enhavo\Bundle\ApiBundle\Endpoint\Context;
use Enhavo\Bundle\UserBundle\Configuration\ConfigurationProvider;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

class LoginDataEndpointType extends AbstractEndpointType
{
    public function __construct(
        private readonly ConfigurationProvider $provider,
        private readonly array $userBrandingParameters,
    )
    {
    }

    public function handleRequest($options, Request $request, Data $data, Context $context)
    {
        if ($this->isGranted('ROLE_USER')) {
            $configuration = $this->provider->getLoginConfiguration();
            $url = $this->generateUrl($configuration->getRedirectRoute());
            if ($request->get('_format') === 'html') {
                $context->setResponse(new RedirectResponse($url));
                return;
            } else {
                $data->set('redirect', $url);
            }
        }

        $data->set('branding', [
            'enableVersion' => $this->userBrandingParameters['enable_version'],
            'enable' => $this->userBrandingParameters['enable'],
            'enableCreatedBy' => $this->userBrandingParameters['enable_created_by'],
            'text' => $this->userBrandingParameters['text'],
            'logo' => $this->userBrandingParameters['logo'],
            'version' => $this->userBrandingParameters['version'],
            'backgroundImage' => $this->userBrandingParameters['background_image'],
        ]);
    }
}