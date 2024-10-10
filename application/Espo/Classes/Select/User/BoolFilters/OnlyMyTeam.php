<?php
/************************************************************************
 * This file is part of EspoCRM.
 *
 * EspoCRM – Open Source CRM application.
 * Copyright (C) 2014-2024 Yurii Kuznietsov, Taras Machyshyn, Oleksii Avramenko
 * Website: https://www.espocrm.com
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program. If not, see <https://www.gnu.org/licenses/>.
 *
 * The interactive user interfaces in modified source and object code versions
 * of this program must display Appropriate Legal Notices, as required under
 * Section 5 of the GNU Affero General Public License version 3.
 *
 * In accordance with Section 7(b) of the GNU Affero General Public License version 3,
 * these Appropriate Legal Notices must retain the display of the "EspoCRM" word.
 ************************************************************************/

namespace Espo\Classes\Select\User\BoolFilters;

use Espo\Entities\Team;
use Espo\Entities\User;
use Espo\Core\Select\Bool\Filter;
use Espo\ORM\Query\Part\Condition;
use Espo\ORM\Query\Part\Expression;
use Espo\ORM\Query\Part\Where\OrGroupBuilder;
use Espo\ORM\Query\Part\WhereClause;
use Espo\ORM\Query\SelectBuilder;

/**
 * @noinspection PhpUnused
 */
class OnlyMyTeam implements Filter
{
    public function __construct(private User $user)
    {}

    public function apply(SelectBuilder $queryBuilder, OrGroupBuilder $orGroupBuilder): void
    {
        $teamIdList = $this->user->getTeamIdList();

        if (count($teamIdList) === 0) {
            $orGroupBuilder->add(
                WhereClause::fromRaw(['id' => null])
            );

            return;
        }

        $orGroupBuilder->add(
            Condition::in(
                Expression::column('id'),
                SelectBuilder::create()
                    ->from(Team::RELATIONSHIP_TEAM_USER)
                    ->select('userId')
                    ->where(['teamId' => $teamIdList])
                    ->build()
            )
        );
    }
}