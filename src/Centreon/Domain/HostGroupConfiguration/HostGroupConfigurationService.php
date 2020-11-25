<?php

/*
 * Copyright 2005 - 2019 Centreon (https://www.centreon.com/)
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

namespace Centreon\Domain\HostGroupConfiguration;

use Centreon\Domain\ActionLog\ActionLog;
use Centreon\Domain\ActionLog\Interfaces\ActionLogServiceInterface;
use Centreon\Domain\Engine\EngineConfiguration;
use Centreon\Domain\Engine\Interfaces\EngineConfigurationServiceInterface;
use Centreon\Domain\HostGroupConfiguration\Interfaces\HostGroupConfigurationRepositoryInterface;
use Centreon\Domain\HostGroupConfiguration\Interfaces\HostGroupConfigurationServiceInterface;
use Centreon\Domain\Repository\RepositoryException;

class HostGroupConfigurationService implements HostGroupConfigurationServiceInterface
{
    /**
     * @var HostGroupConfigurationRepositoryInterface
     */
    private $hostGroupConfigurationRepository;
    /**
     * @var EngineConfigurationServiceInterface
     */
    private $engineConfigurationService;

    /**
     * @var ActionLogServiceInterface
     */
    private $actionLogService;

    /**
     * @param HostGroupConfigurationRepositoryInterface $hostGroupConfigurationRepository
     * @param ActionLogServiceInterface $actionLogService
     * @param EngineConfigurationServiceInterface $engineConfigurationService
     */
    public function __construct(
        HostGroupConfigurationRepositoryInterface $hostGroupConfigurationRepository,
        ActionLogServiceInterface $actionLogService,
        EngineConfigurationServiceInterface $engineConfigurationService
    ) {
        $this->hostGroupConfigurationRepository = $hostGroupConfigurationRepository;
        $this->actionLogService = $actionLogService;
        $this->engineConfigurationService = $engineConfigurationService;
    }

    /**
     * @inheritDoc
     */
    public function addHostGroup(HostGroup $hostGroup): int
    {
        if (empty($hostGroup->getName())) {
            throw new HostGroupConfigurationException(_('Host name can not be empty'));
        }
        try {
            if (empty($hostGroup->getIpAddress())) {
                throw new HostGroupConfigurationException(_('Ip address can not be empty'));
            }

            if ($hostGroup->getMonitoringServer() === null || $hostGroup->getMonitoringServer()->getName() === null) {
                throw new HostGroupConfigurationException(_('Monitoring server is not correctly defined'));
            }

            /*
             * To avoid defining a host name with illegal characters,
             * we retrieve the engine configuration to retrieve the list of these characters.
             */
            $engineConfiguration = $this->engineConfigurationService->findEngineConfigurationByName(
                $hostGroup->getMonitoringServer()->getName()
            );
            if ($engineConfiguration === null) {
                throw new HostGroupConfigurationException(_('Unable to find the Engine configuration'));
            }

            $safedHostName = EngineConfiguration::removeIllegalCharacters(
                $hostGroup->getName(),
                $engineConfiguration->getIllegalObjectNameCharacters()
            );
            if (empty($safedHostName)) {
                throw new HostGroupConfigurationException(_('Host name can not be empty'));
            }
            $hostGroup->setName($safedHostName);

            if ($this->hostGroupConfigurationRepository->hasHostWithSameName($hostGroup->getName())) {
                throw new HostGroupConfigurationException(_('Host name already exists'));
            }
            if ($hostGroup->getExtendedHost() === null) {
                $hostGroup->setExtendedHost(new ExtendedHost());
            }

            if ($hostGroup->getMonitoringServer()->getId() === null) {
                $hostGroup->getMonitoringServer()->setId($engineConfiguration->getMonitoringServerId());
            }
            $hostId = $this->hostGroupConfigurationRepository->addHostGroup($hostGroup);
            $defaultStatus = 'Default';

            // We create the list of changes concerning the creation of the host
            $actionsDetails = [
                'Host name' => $hostGroup->getName() ?? '',
                'Host alias' => $hostGroup->getAlias() ?? '',
                'Host IP address' => $hostGroup->getIpAddress() ?? '',
                'Monitoring server name' => $hostGroup->getMonitoringServer()->getName() ?? '',
                'Create services linked to templates' => 'true',
                'Is activated' => $hostGroup->isActivated() ? 'true' : 'false',

                // We don't have these properties in the host object yet, so we display these default values
                'Active checks enabled' => $defaultStatus,
                'Passive checks enabled' => $defaultStatus,
                'Notifications enabled' => $defaultStatus,
                'Obsess over host' => $defaultStatus,
                'Check freshness' => $defaultStatus,
                'Flap detection enabled' => $defaultStatus,
                'Retain status information' => $defaultStatus,
                'Retain nonstatus information' => $defaultStatus,
                'Event handler enabled' => $defaultStatus,
            ];
            if (!empty($hostGroup->getTemplates())) {
                $templateNames = [];
                foreach ($hostGroup->getTemplates() as $template) {
                    if (!empty($template->getName())) {
                        $templateNames[] = $template->getName();
                    }
                }
                $actionsDetails = array_merge($actionsDetails, ['Templates selected' => implode(', ', $templateNames)]);
            }

            if (!empty($hostGroup->getMacros())) {
                $macroDetails = [];
                foreach ($hostGroup->getMacros() as $macro) {
                    if (!empty($macro->getName())) {
                        // We remove the symbol characters in the macro name
                        $macroDetails[substr($macro->getName(), 2, strlen($macro->getName()) - 3)] =
                            $macro->isPassword() ? '*****' : $macro->getValue() ?? '';
                    }
                }
                $actionsDetails = array_merge($actionsDetails, [
                    'Macro names' => implode(', ', array_keys($macroDetails)),
                    'Macro values' => implode(', ', array_values($macroDetails))
                ]);
            }
            $this->actionLogService->addAction(
                // The userId is set to 0 because it is not yet possible to determine who initiated the action.
                // We will see later how to get it back.
                new ActionLog('hostGroup', $hostId, $hostGroup->getName(), ActionLog::ACTION_TYPE_ADD, 0),
                $actionsDetails
            );
            return $hostId;
        } catch (HostGroupConfigurationException $ex) {
            throw $ex;
        } catch (RepositoryException $ex) {
            throw new HostGroupConfigurationException($ex->getMessage(), 0, $ex);
        } catch (\Exception $ex) {
            throw new HostGroupConfigurationException(_('Error while creation of host'), 0, $ex);
        }
    }

    /**
     * @inheritDoc
     */
    public function findAndAddHostTemplates(HostGroup $host): void
    {
        try {
            $this->hostGroupConfigurationRepository->findAndAddHostTemplates($host);
        } catch (\Throwable $ex) {
            throw new HostGroupConfigurationException(_('Error when searching for host templates'), 0, $ex);
        }
    }

    /**
     * @inheritDoc
     */
    public function findHostGroup(int $hostGroupId): ?HostGroup
    {
        try {
            return $this->hostGroupConfigurationRepository->findHostGroup($hostGroupId);
        } catch (\Throwable $ex) {
            throw new HostGroupConfigurationException(_('Error while searching for the host'), 0, $ex);
        }
    }
}
