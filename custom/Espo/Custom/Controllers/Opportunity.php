<?php

namespace Espo\Custom\Controllers;

use Espo\Core\Acl;
use Espo\Core\AclManager;
use Espo\Core\Api\Request;
use Espo\Core\Api\Response;
use Espo\Core\Container;
use Espo\Core\Exceptions\BadRequest;
use Espo\Core\Exceptions\Forbidden;
use Espo\Core\Exceptions\NotFoundSilent;
use Espo\Core\Record\CreateParamsFetcher;
use Espo\Core\Record\DeleteParamsFetcher;
use Espo\Core\Record\FindParamsFetcher;
use Espo\Core\Record\ReadParamsFetcher;
use Espo\Core\Record\SearchParamsFetcher;
use Espo\Core\Record\ServiceContainer as RecordServiceContainer;
use Espo\Core\Record\UpdateParamsFetcher;
use Espo\Core\ServiceFactory;
use Espo\Core\Utils\Config;
use Espo\Core\Utils\Json;
use Espo\Core\Utils\Metadata;
use Espo\Entities\Preferences;
use Espo\Entities\User;
use stdClass;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class Opportunity extends \Espo\Modules\Crm\Controllers\Opportunity
{


    private HttpClientInterface $client;

    public function __construct(SearchParamsFetcher $searchParamsFetcher, CreateParamsFetcher $createParamsFetcher, ReadParamsFetcher $readParamsFetcher, UpdateParamsFetcher $updateParamsFetcher, DeleteParamsFetcher $deleteParamsFetcher, RecordServiceContainer $recordServiceContainer, FindParamsFetcher $findParamsFetcher, Config $config, User $user, Acl $acl, Container $container, AclManager $aclManager, Preferences $preferences, Metadata $metadata, ServiceFactory $serviceFactory)
    {

        $this->client = HttpClient::create();

        parent::__construct($searchParamsFetcher, $createParamsFetcher, $readParamsFetcher, $updateParamsFetcher, $deleteParamsFetcher, $recordServiceContainer, $findParamsFetcher, $config, $user, $acl, $container, $aclManager, $preferences, $metadata, $serviceFactory);
    }


    /**
     * @param Request $request
     * @param Response $response
     * @return stdClass
     * @throws BadRequest
     * @throws Forbidden
     * @throws NotFoundSilent
     * @throws TransportExceptionInterface
     */
    public function getActionRead(Request $request, Response $response): \stdClass
    {
        if (method_exists($this, 'actionRead')) {
            // For backward compatibility.
            return (object) $this->actionRead($request->getRouteParams(), $request->getParsedBody(), $request);
        }



        $id = $request->getRouteParam('id');
        $params = $this->readParamsFetcher->fetch($request);

        if (!$id) {
            throw new BadRequest("No ID.");
        }

        $entity = $this->getRecordService()->read($id, $params);

        /** @var \Espo\Modules\Crm\Entities\Lead $originalLead */
        $originalLead = $entity->get('originalLead');

        $response = $this->client->request(
            'GET',
            'https://n8n.walvis.dev/webhook/41db1730-079e-41cb-99b8-35f0bbfb4367',
            [
                'body' => [
                    'email' => $originalLead->getEmailAddress(),
                    'id' => $id,
                ]
            ]
        );
        $response = Json::decode($response->getContent())->data;
        $entity->set('cLastEvent', $response->event);



        return $entity->getValueMap();

    }

}