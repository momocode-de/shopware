<?php declare(strict_types=1);
/**
 * Shopware 5
 * Copyright (c) shopware AG
 *
 * According to our dual licensing model, this program can be used either
 * under the terms of the GNU Affero General Public License, version 3,
 * or under a proprietary license.
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission and of our proprietary license can be found at and
 * in the LICENSE file you have received along with this program.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * "Shopware" is a registered trademark of shopware AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore any rights, title and interest in
 * our trademarks remain entirely with us.
 */

namespace Shopware\Api\Write\Query;

use Doctrine\DBAL\Connection;
use Shopware\Api\Entity\EntityDefinition;

class UpdateQuery extends WriteQuery
{
    /**
     * @var array
     */
    private $pkData;

    /**
     * @var array
     */
    private $payload;

    /**
     * @var string|EntityDefinition
     */
    private $definition;

    public function __construct(string $definition, array $pkData, array $payload)
    {
        $this->pkData = $pkData;
        $this->payload = $payload;
        $this->definition = $definition;
    }

    public function isExecutable(): bool
    {
        return (bool) count($this->payload);
    }

    public function execute(Connection $connection): int
    {
        $table = $this->definition::getEntityName();

        return $connection->update('`' . $table . '`', $this->payload, $this->pkData);
    }

    public function getPayload(): array
    {
        return $this->payload;
    }

    public function getPrimaryKeyData(): array
    {
        return $this->pkData;
    }

    public function getEntityDefinition(): string
    {
        return $this->definition;
    }

    public function getEntityPrimaryKey()
    {
        if (count($this->pkData) === 1) {
            $tmp = array_values($this->pkData);

            return $tmp[0];
        }

        return $this->pkData;
    }
}
