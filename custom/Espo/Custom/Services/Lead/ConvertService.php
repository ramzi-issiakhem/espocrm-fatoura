<?php

namespace Espo\Custom\Services\Lead;

use Espo\Core\Exceptions\BadRequest;
use Espo\Core\Exceptions\Conflict;
use Espo\Core\Exceptions\ConflictSilent;
use Espo\Core\Exceptions\Forbidden;
use Espo\Modules\Crm\Entities\Account;
use Espo\Modules\Crm\Entities\Contact;
use Espo\Modules\Crm\Entities\Lead;
use Espo\Modules\Crm\Entities\Opportunity;
use Espo\Modules\Crm\Tools\Lead\Convert\Values;
use Espo\Modules\Crm\Tools\Lead\ConvertService as ParentConvertService;
use Espo\ORM\Entity;

class ConvertService extends ParentConvertService
{

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


        $isLicenseIssued =  $lead->get('cLicenseIssued');
        if ($isLicenseIssued !== true) {
            throw(new ConflictSilent('The License Field in the lead must be issued to convert it'));
        }

        $invoiceNumber = $lead->get('cInvoiceNumber');
        if (empty($invoiceNumber)) {
            throw(new ConflictSilent('The Invoice Number Field in the lead must be filled to convert it'));
        }

        $quoteNumber = $lead->get('cQuoteNumber');
        if (empty($quoteNumber)) {
            throw(new ConflictSilent('The Quote Number Field in the lead must be filled to convert it'));
        }

        $opportunity = parent::processOpportunity($lead, $records, $duplicateCheck, $duplicateList, $skipSave, $account, $contact);
        $opportunity->set('cPhoneNumber', $lead->get('phoneNumber'));

        return parent::processOpportunity($lead, $records, $duplicateCheck, $duplicateList, $skipSave, $account, $contact);

    }


}