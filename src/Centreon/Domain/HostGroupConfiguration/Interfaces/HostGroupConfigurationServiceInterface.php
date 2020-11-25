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

namespace Centreon\Domain\HostGroupConfiguration\Interfaces;

use Centreon\Domain\HostGroupConfiguration\HostGroup;
use Centreon\Domain\HostGroupConfiguration\HostGroupConfigurationException;

interface HostGroupConfigurationServiceInterface
{
    /**
     * Add a host.
     *
     * @param HostGroup $hostGroup
     * @return int Returns the host id
     * @throws HostGroupConfigurationException
     */
    public function addHostGroup(HostGroup $hostGroup): int;

    /**
     * Find a host.
     *
     * @param int $hostGroupId Host Id to be found
     * @return HostGroup|null Returns a host otherwise null
     * @throws HostGroupConfigurationException
     */
    public function findHostGroup(int $hostGroupId): ?HostGroup;
    
    /**
     * Find and add all host templates in the given host.
     *
     * **The priority order of host templates is maintained!**
     *
     * @param HostGroup $host Host for which we want to find and add all host templates
     * @throws HostGroupConfigurationException
     */
    public function findAndAddHostTemplates(HostGroup $host): void;
    
}
