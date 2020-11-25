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

namespace Centreon\Domain\HostGroupConfiguration;

use Centreon\Domain\MonitoringServer\MonitoringServer;
use Centreon\Domain\Annotation\EntityDescriptor;

/***
 * This class is designed to represent a host configuration.
 *
 * @package Centreon\Domain\HostConfiguration
 */
class HostGroup
{
    /**
     * @var int|null
     */
    private $id;

    /**
     * @var string|null
     */
    private $name;

    /**
     * @var string|null
     */
    private $alias;

    /**
     * @var string|null
     */
    private $notes;

    /**
     * @var string|null
     */
    private $notesUrl;

    /**
     * @var string|null
     */
    private $actionUrl;

    /**
     * @var string|null
     */
    private $icon;

    /**
     * @var string|null
     */
    private $iconMap;

    /**
     * @var string|null
     */
    private $rrd;

    /**
     * @var string|null
     */
    private $geoCoords;

    /**
     * @var string|null
     */
    private $comment;

    /**icon
     * @var bool
     * @EntityDescriptor(column="is_activated", modifier="setActivted")
     */
    private $isActivated = true;

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @param int|null $id
     * @return HostGroup
     */
    public function setId(?int $id): HostGroup
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return HostGroup
     */
    public function setName(string $name): HostGroup
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getAlias(): ?string
    {
        return $this->alias;
    }

    /**
     * @param string|null $alias
     * @return HostGroup
     */
    public function setAlias(?string $alias): HostGroup
    {
        $this->alias = $alias;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getNotes(): ?string
    {
        return $this->notes;
    }

    /**
     * @param string|null $notes
     * @return HostGroup
     */
    public function setNotes(?string $notes): HostGroup
    {
        $this->notes = $notes;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getNotesUrl(): ?string
    {
        return $this->notesUrl;
    }

    /**
     * @param string|null $notesUrl
     * @return HostGroup
     */
    public function setNotesUrl(?string $notesUrl): HostGroup
    {
        $this->notesUrl = $notesUrl;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getActionUrl(): ?string
    {
        return $this->actionUrl;
    }

    /**
     * @param string|null $actionUrl
     * @return HostGroup
     */
    public function setActionUrl(?string $actionUrl): HostGroup
    {
        $this->actionUrl = $actionUrl;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getIcon(): ?string
    {
        return $this->icon;
    }

    /**
     * @param string|null $icon
     * @return HostGroup
     */
    public function setIcon(?string $icon): HostGroup
    {
        $this->icon = $icon;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getIconMap(): ?string
    {
        return $this->iconMap;
    }

    /**
     * @param string|null $iconMap
     * @return HostGroup
     */
    public function setIconMap(?string $iconMap): HostGroup
    {
        $this->iconMap = $iconMap;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getRrd(): ?string
    {
        return $this->rrd;
    }

    /**
     * @param string|null $rrd
     * @return HostGroup
     */
    public function setRrd(?string $rrd): HostGroup
    {
        $this->rrd = $rrd;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getGeoCoords(): ?string
    {
        return $this->geoCoords;
    }

    /**
     * @param string|null $geoCoords
     * @return HostGroup
     */
    public function setGeoCoords(?string $geoCoords): HostGroup
    {
        $this->geoCoords = $geoCoords;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getComment(): ?string
    {
        return $this->comment;
    }

    /**
     * @param string|null $comment
     * @return HostGroup
     */
    public function setComment(?string $comment): HostGroup
    {
        $this->comment = $comment;
        return $this;
    }

    /**
     * @return bool
     */
    public function isActivated(): bool
    {
        return $this->isActivated;
    }

    /**
     * @param bool $isActivated
     * @return HostGroup
     */
    public function setActivated(bool $isActivated): HostGroup
    {
        $this->isActivated = $isActivated;
        return $this;
    }
}
