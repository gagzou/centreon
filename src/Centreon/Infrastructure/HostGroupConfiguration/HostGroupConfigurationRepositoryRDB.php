<?php

/*
 * Copyright 2005 - 2020 Centreon (https://www.centreon.com/)
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 *
 * For more information : contact@centreon.com
 *
 */
declare(strict_types=1);

namespace Centreon\Infrastructure\HostGroupConfiguration;

use Centreon\Domain\Entity\EntityCreator;
use Centreon\Domain\HostConfiguration\Host;
use Centreon\Domain\HostGroupConfiguration\Interfaces\HostGroupConfigurationRepositoryInterface;
use Centreon\Infrastructure\DatabaseConnection;
use Centreon\Infrastructure\Repository\AbstractRepositoryDRB;
use Centreon\Infrastructure\RequestParameters\SqlRequestParametersTranslator;

class HostGroupConfigurationRepositoryRDB extends AbstractRepositoryDRB implements HostConfigurationRepositoryInterface
{

    /**
     * @var SqlRequestParametersTranslator
     */
    private $sqlRequestTranslator;

    public function __construct(DatabaseConnection $db, SqlRequestParametersTranslator $sqlRequestTranslator)
    {
        $this->db = $db;
        $this->sqlRequestTranslator = $sqlRequestTranslator;
        $this->sqlRequestTranslator
            ->getRequestParameters()
            ->setConcordanceStrictMode(RequestParameters::CONCORDANCE_MODE_STRICT);
    }
    
    /**
     * @inheritDoc
     */
    public function addHostGroup(HostGroup $hostGroup): int
    {
        try {
            $this->db->beginTransaction();
            
            $request = $this->translateDbName(
                'INSERT INTO `:db`.hostgroup
                (hg_name, hg_alias, hg_notes, hg_notes_url, hg_action_url, hg_icon_image, hg_map_icon_image,
                hg_rrd_retention, geo_coords, hg_comment, hg_activate)
                VALUES (:name, :alias, :notes, :notesUrl, :actionUrl, :icon,  :iconMap,
                        :rrd, :geo_coords, :comment, :is_activate)'
            );
            $statement = $this->db->prepare($request);
            $statement->bindValue(':name', $hostGroup->getName(), \PDO::PARAM_STR);
            empty($hostGroup->getAlias())
                ? $statement->bindValue(':alias', null, \PDO::PARAM_NULL)
                : $statement->bindValue(':alias', $hostGroup->getAlias(), \PDO::PARAM_STR);
    
            
            $statement->bindValue(':notes', $hostGroup->getNotes(), \PDO::PARAM_STR);
            $statement->bindValue(':notesUrl', $hostGroup->getNotesUrl(), \PDO::PARAM_STR);
            $statement->bindValue(':actionUrl', $hostGroup->getActionUrl(), \PDO::PARAM_STR);
            $statement->bindValue(':icon', $hostGroup->getIcon(), \PDO::PARAM_STR);
            $statement->bindValue(':iconMap', $hostGroup->getIconMap(), \PDO::PARAM_STR);
            $statement->bindValue(':rrd', $hostGroup->getRrd(), \PDO::PARAM_STR);
            $statement->bindValue(':geo_coords', $hostGroup->getGeoCoords(), \PDO::PARAM_STR);
            $statement->bindValue(':comment', $hostGroup->getComment(), \PDO::PARAM_STR);
            $statement->bindValue(':is_activate', $hostGroup->isActivated(), \PDO::PARAM_STR);
            $statement->execute();
    
            $hostGroupId = (int)$this->db->lastInsertId();
            /*
            if ($host->getMonitoringServer() !== null) {
                $this->addMonitoringServer($hostId, $host->getMonitoringServer());
            }
            if ($host->getExtendedHost() !== null) {
                $this->addExtendedHost($hostId, $host->getExtendedHost());
            }
            $this->addHostTemplate($hostId, $host->getTemplates());
            $this->addHostMacro($hostId, $host->getMacros());
*/
            
            $this->db->commit();
            
            return $hostGroupId;
        } catch (\Exception $ex) {
            $this->db->rollBack();
            throw $ex;
        }
    }
    
    
    /**
     * @inheritDoc
     */
    public function findHostGroup(int $hostGroupId): ?HostGroup
    {
        $request = $this->translateDbName(
            'SELECT host.host_id, host.host_name, host.host_alias, host.display_name AS host_display_name,
            host.host_address AS host_ip_address, host.host_comment, host.geo_coords AS host_geo_coords,
            host.host_activate AS host_is_activated, nagios.id AS monitoring_server_id,
            nagios.name AS monitoring_server_name, ext.*
            FROM `:db`.host host
            LEFT JOIN `:db`.extended_host_information ext
                ON ext.host_host_id = host.host_id
            INNER JOIN `:db`.ns_host_relation host_server
                ON host_server.host_host_id = host.host_id
            INNER JOIN `:db`.nagios_server nagios
                ON nagios.id = host_server.nagios_server_id
            WHERE host.host_id = :host_id
            AND host.host_register = \'1\''
        );
        $statement = $this->db->prepare($request);
        $statement->bindValue(':host_id', $hostGroupId, \PDO::PARAM_INT);
        $statement->execute();

        if (($record = $statement->fetch(\PDO::FETCH_ASSOC)) !== false) {
            /**
             * @var Host $host
             */
            $host = EntityCreator::createEntityByArray(Host::class, $record, 'host_');
            /**
             * @var ExtendedHost $extendedHost
             */
            $extendedHost = EntityCreator::createEntityByArray(ExtendedHost::class, $record, 'ehi_');
            $host->setExtendedHost($extendedHost);
            /**
             * @var MonitoringServer $monitoringServer
             */
            $monitoringServer = EntityCreator::createEntityByArray(
                MonitoringServer::class,
                $record,
                'monitoring_server_'
            );
            $host->setMonitoringServer($monitoringServer);

            return $host;
        }
        return null;
    }
   
}
