<?php

namespace Espo\Custom\Hooks\Lead;

use Espo\Core\Exceptions\ConflictSilent;
use Espo\Core\Field\DateTime;
use Espo\Core\Hook\Hook\BeforeSave;
use Espo\Core\Record\CreateParams;
use Espo\Modules\Crm\Entities\Call;
use Espo\Modules\Crm\Entities\Lead;
use Espo\Modules\Crm\Entities\Reminder;
use Espo\ORM\Entity;
use Espo\ORM\EntityManager;
use Espo\ORM\Repository\Option\SaveOptions;
use Espo\Core\Record\ServiceContainer;
use \Espo\Core\Record\Service;
use Espo\ORM\Repository\RDBRepository;

class LeadBeforeSave implements BeforeSave
{

    private RDBRepository $callRepository ;

    private EntityManager $entityManager;

    private Service $leadRecord;
    private Service $reminderRecord;

    public function __construct(EntityManager $entityManager,ServiceContainer $serviceContainer)
    {
        $this->entityManager = $entityManager;
        $this->callRepository = $entityManager->getRDBRepository(Call::ENTITY_TYPE);
        $this->leadRecord     = $serviceContainer->get(Lead::ENTITY_TYPE);
        $this->reminderRecord = $serviceContainer->get(Reminder::ENTITY_TYPE);
    }

    /**
     * @throws \Exception
     */
    public function beforeSave(Entity $entity, SaveOptions $options): void
    {
        $stage = $entity->get('status');
        $leadCallStatus = $entity->get("cCallStatus");

        // Set the Assigned User to the Created By User if not defined
        if ($entity->isNew()) {
            if ($entity->get('assignedUserId') == null) {
                $entity->set('assignedUserId', $entity->get('createdById') );
            }
        }

        // Check if the lead is going to Won STage and if the quote number is defined
        if ($entity->isAttributeChanged('status')) {
            if ($stage == "Won") {

                if ($entity->get('cSubscriptionAdded') !== true) {
                    throw new ConflictSilent('The Subscription Field in the lead must be validated to transfer it  to the Won stage');
                }

                if ($entity->get('cQuoteNumber') == null) {
                    throw new ConflictSilent('The Quote Number Field in the lead must be filled to transfer it to the Won stage');
                }
            }
        }

        if ($entity->isAttributeChanged('cCallStatus')) {
            if (in_array($leadCallStatus, ["Call Back", "Did not pick up"])) {
                $name = 'Call - ' . $entity->get('name');
                $callEntity = $this->callRepository->where(['name' => $name])->findOne();


                if ($callEntity == null) {
                    $callEntity = $this->entityManager->getEntity(Call::ENTITY_TYPE);
                }

                // Create a Delay of 3 Days
                $dateTime = DateTime::createNow();
                $dateTime = $dateTime->withTime(9, 0, 0)->addDays(3)->toString();

                // Create a Call
                $callEntity->set('dateStart', $dateTime);
                $callEntity->set('name', $name);
                $callEntity->set('description', 'Call Back');
                $callEntity->set('assignedUserId', $entity->get('assignedUserId'));
                $callEntity->set('teamsIds', $entity->get('teamsIds'));
                $this->entityManager->saveEntity($callEntity);

                // Create a Reminder and link it to the call
                $data = (object)[
                    'type' => Reminder::TYPE_POPUP,
                    'seconds' => 0,
                    'entityType' => Call::ENTITY_TYPE,
                    'entityId' => $callEntity->getId(),
                    'userId' => $entity->get('assignedUserId'),
                ];
                $this->reminderRecord->create($data, CreateParams::create());

                // Link the Call to the Lead
                $this->leadRecord->link($entity->getId(), 'calls', $callEntity->getId());
            }
            else if ($leadCallStatus == "Wrong Info" || $leadCallStatus == "Not Interested") {
                $entity->set('status', "Lost");
            }


        }
    }


}