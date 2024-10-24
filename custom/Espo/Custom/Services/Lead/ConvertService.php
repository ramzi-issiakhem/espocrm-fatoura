<?php

namespace Espo\Custom\Services\Lead;

use Espo\Core\Acl;
use Espo\Core\Exceptions\BadRequest;
use Espo\Core\Exceptions\Conflict;
use Espo\Core\Exceptions\ConflictSilent;
use Espo\Core\Exceptions\Forbidden;
use Espo\Core\Record\ServiceContainer;
use Espo\Core\Utils\FieldUtil;
use Espo\Core\Utils\Metadata;
use Espo\Entities\User;
use Espo\Modules\Crm\Entities\Account;
use Espo\Modules\Crm\Entities\Contact;
use Espo\Modules\Crm\Entities\Lead;
use Espo\Modules\Crm\Entities\Opportunity;
use Espo\Modules\Crm\Tools\Lead\Convert\Values;
use Espo\Modules\Crm\Tools\Lead\ConvertService as ParentConvertService;
use Espo\ORM\Entity;
use Espo\ORM\EntityManager;
use Espo\Tools\Stream\Service as StreamService;

class ConvertService extends ParentConvertService
{

    public function __construct(Acl $acl, ServiceContainer $recordServiceContainer, EntityManager $entityManager, User $user, StreamService $streamService, Metadata $metadata, FieldUtil $fieldUtil)
    {
        parent::__construct($acl, $recordServiceContainer, $entityManager, $user, $streamService, $metadata, $fieldUtil);
    }

    /**
     * @param Entity[] $duplicateList
     * @throws Forbidden
     * @throws BadRequest
     * @throws Conflict
     */
    protected function processOpportunity(
        Lead $lead,
        Values $records,
        bool $duplicateCheck,
        array &$duplicateList,
        bool &$skipSave,
        ?Account $account,
        ?Contact $contact,
    ): ?Opportunity {

        $stage = $lead->get('status');
        if ($stage !== 'Won') {
            throw new ConflictSilent('Lead must be in Won stage to convert it');
        }

        $isSubscriptionAdded =  $lead->get('cSubscriptionAdded');
        if ($isSubscriptionAdded !== true) {
            throw(new ConflictSilent('The Subscription Field in the lead must be validated to convert it'));
        }


        $invoiceNumber = $lead->get('cInvoiceNumber');
        if (empty($invoiceNumber)) {
            throw(new ConflictSilent('The Invoice Number Field in the lead must be filled to convert it'));
        }

        $quoteNumber = $lead->get('cQuoteNumber');
        if (empty($quoteNumber)) {
            throw(new ConflictSilent('The Quote Number Field in the lead must be filled to convert it'));
        }

        $clientValues = $records->get(Opportunity::ENTITY_TYPE);
        $clientValues->name         = $lead->get('name');
        $clientValues->cEmail       = $lead->get('emailAddress');

        $clientValues->cPhoneNumber = $lead->getPhoneNumberGroup()->getNumberList();
        
        $records = $records->with(Opportunity::ENTITY_TYPE, $clientValues);

        $opportunity = parent::processOpportunity($lead, $records, $duplicateCheck, $duplicateList, $skipSave, $account, $contact);
        return $opportunity;
    }


}